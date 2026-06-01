---
description: Integración de WhatsApp via Twilio con arquitectura de driver reusable para soportar Meta API en el futuro; el agente de ventas atiende mensajes entrantes de WhatsApp de forma asíncrona
type: técnico
date: 2026-05-31
status: draft
user: Irwing Naranjo
---

# WhatsApp: Integración con Twilio (driver reusable)

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

El agente de ventas puede atender a clientes que escriben por **WhatsApp** sin intervención humana. Los mensajes que llegan al número de WhatsApp Business de la empresa se procesan automáticamente por el `SalesAgent` y la respuesta se envía de regreso al cliente en el mismo chat.

La integración se implementa inicialmente con **Twilio** (por simplicidad y costo de prueba), pero la arquitectura debe permitir migrar a la **API de Meta (WhatsApp Business API directa)** sin reescribir la lógica central.

### Actores y roles

| Actor               | Comportamiento                                                                           |
| ------------------- | ---------------------------------------------------------------------------------------- |
| Cliente (WhatsApp)  | Escribe al número de WhatsApp Business; recibe respuesta del agente de IA.               |
| SalesAgent (IA)     | Procesa el mensaje, usa tools de ventas y genera la respuesta.                           |
| Twilio              | Recibe el mensaje de WhatsApp, lo reenvía como webhook al servidor, y entrega la respuesta. |

### Flujo principal

1. El cliente envía un mensaje a la línea de WhatsApp Business configurada en Twilio.
2. Twilio recibe el mensaje y hace un `POST` al webhook del servidor.
3. El servidor valida la firma de Twilio y extrae el teléfono y el texto.
4. Si el cliente no tiene conversación activa, se crea una nueva (equivalente a `/chatbot/iniciar`).
5. El mensaje se encola para procesamiento asíncrono (el agente puede tardar 10–30 segundos).
6. Twilio recibe `HTTP 200` inmediatamente (sin TwiML con contenido).
7. En background, el job llama al `SalesAgent`, obtiene la respuesta y la envía al cliente vía la API REST de Twilio.
8. Si el agente adjunta videos de productos, se envían como mensajes de media separados.

### Reglas de negocio

- Un número de WhatsApp tiene a lo sumo **una conversación activa** por empresa (misma regla que el canal web).
- Si el cliente ya tiene conversación activa, el mensaje se agrega a esa conversación.
- Si no tiene conversación activa, se crea una nueva automáticamente (sin pedir confirmación como en el web widget).
- El `source` de la conversación y de los mensajes se registra como `whatsapp`.
- Los adjuntos multimedia entrantes (imágenes, documentos) del cliente **no se procesan** en esta entrega; solo el texto.
- Si el agente tarda más de N segundos (configurable), se envía un mensaje de espera al cliente.
- Las respuestas que superen el límite de caracteres de WhatsApp (4096) se envían en múltiples mensajes.

---

## ESPECIFICACIÓN TÉCNICA

### Principio de diseño: Driver Pattern

El código se organiza en **dos capas**:

```
┌─────────────────────────────────────────────────────────────────────┐
│  Capa de canal (driver)                                             │
│  - Validación de firma de webhook                                   │
│  - Parseo del request al formato interno                            │
│  - Envío de mensajes via la API del proveedor                       │
│  Implementaciones: TwilioWhatsappDriver, MetaWhatsappDriver (futuro)│
└───────────────────────────────┬─────────────────────────────────────┘
                                │  WhatsappIncomingMessage (DTO)
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│  Capa de procesamiento (compartida)                                 │
│  - WhatsappMessageProcessor                                         │
│  - Reutiliza IniciarChatbot + SendChatbotMessage actions existentes │
│  - Independiente del proveedor                                      │
└─────────────────────────────────────────────────────────────────────┘
```

**Ventaja al migrar a Meta**: solo se agrega `MetaWhatsappDriver` + su controlador de webhook. El procesador, las actions y los jobs no cambian.

---

### Interface del driver

```php
// app/Contracts/WhatsappDriver.php

interface WhatsappDriver
{
    /**
     * Valida que el webhook proviene del proveedor legítimo.
     * Lanza HttpException(403) si la firma no es válida.
     */
    public function verifyWebhook(Request $request): void;

    /**
     * Convierte el request del proveedor en el DTO interno.
     * Retorna null si el evento no es un mensaje de texto (ej. status update).
     */
    public function parseIncomingMessage(Request $request): ?WhatsappIncomingMessage;

    /**
     * Envía un mensaje de texto al número de destino.
     */
    public function sendTextMessage(string $to, string $body): void;

    /**
     * Envía un mensaje con media (video, imagen) al número de destino.
     */
    public function sendMediaMessage(string $to, string $mediaUrl, string $caption = ''): void;
}
```

### DTO interno

```php
// app/Whatsapp/WhatsappIncomingMessage.php

readonly class WhatsappIncomingMessage
{
    public function __construct(
        public string $phone,      // normalizado, ej. "+56912345678"
        public string $body,       // texto del mensaje
        public string $messageId,  // ID único del proveedor (para idempotencia)
        public string $rawFrom,    // valor original del remitente, ej. "whatsapp:+56912345678"
        public string $toNumber,   // número de destino (el de la empresa), ej. "+14155238886"
                                   // En el futuro se usará para resolver la empresa cuando haya múltiples
    ) {}
}
```

---

### Implementación Twilio

#### Cómo funciona Twilio WhatsApp

- Twilio hace `POST` al webhook con `application/x-www-form-urlencoded`.
- Campos relevantes: `From` (ej. `whatsapp:+56912345678`), `Body` (texto), `MessageSid` (ID único), `MediaUrl0`..`MediaUrlN` (adjuntos).
- La firma se verifica con el header `X-Twilio-Signature` usando el Auth Token de Twilio.
- Para responder, el servidor puede retornar TwiML **o** enviar el mensaje vía REST API y retornar `200` vacío. Se usará **REST API** (necesario para respuestas asíncronas).

#### TwilioWhatsappDriver

```php
// app/Whatsapp/Drivers/TwilioWhatsappDriver.php

class TwilioWhatsappDriver implements WhatsappDriver
{
    public function __construct(
        private string $accountSid,
        private string $authToken,
        private string $fromNumber,   // ej. "whatsapp:+14155238886"
    ) {}

    public function verifyWebhook(Request $request): void
    {
        // Usar twilio/sdk: \Twilio\Security\RequestValidator
        $validator = new RequestValidator($this->authToken);
        $url = $request->url();
        $params = $request->all();
        $signature = $request->header('X-Twilio-Signature', '');

        if (! $validator->validate($signature, $url, $params)) {
            abort(403, 'Invalid Twilio signature');
        }
    }

    public function parseIncomingMessage(Request $request): ?WhatsappIncomingMessage
    {
        // Los status callbacks de Twilio no tienen 'Body'; ignorarlos
        if (! $request->has('Body') || ! $request->has('From')) {
            return null;
        }

        $rawFrom = $request->input('From'); // "whatsapp:+56912345678"
        $phone = str_replace('whatsapp:', '', $rawFrom);

        return new WhatsappIncomingMessage(
            phone: $phone,
            body: $request->input('Body'),
            messageId: $request->input('MessageSid'),
            rawFrom: $rawFrom,
        );
    }

    public function sendTextMessage(string $to, string $body): void
    {
        // Envío via twilio/sdk REST client
        // Si $body supera 4096 caracteres, dividir en chunks
        $chunks = $this->splitMessage($body, 4096);

        $client = new \Twilio\Rest\Client($this->accountSid, $this->authToken);

        foreach ($chunks as $chunk) {
            $client->messages->create("whatsapp:{$to}", [
                'from' => $this->fromNumber,
                'body' => $chunk,
            ]);
        }
    }

    public function sendMediaMessage(string $to, string $mediaUrl, string $caption = ''): void
    {
        $client = new \Twilio\Rest\Client($this->accountSid, $this->authToken);

        $client->messages->create("whatsapp:{$to}", [
            'from'     => $this->fromNumber,
            'body'     => $caption,
            'mediaUrl' => [$mediaUrl],
        ]);
    }

    private function splitMessage(string $body, int $maxLength): array
    {
        // Divide por párrafos o palabras respetando el límite
        if (mb_strlen($body) <= $maxLength) {
            return [$body];
        }
        // ... lógica de split
    }
}
```

#### Binding en AppServiceProvider

Ver la sección **"Configuración de credenciales y selección de proveedor activo"** para el detalle del flujo y el código del binding. El binding lee `config('whatsapp.driver')` que ya fue resuelto por `WhatsappConfigurationBridge::apply()` en `boot()`.

---

### Webhook controller

```php
// app/Http/Controllers/Webhooks/TwilioWhatsappController.php

class TwilioWhatsappController extends Controller
{
    public function __invoke(Request $request, WhatsappDriver $driver): Response
    {
        // 1. Validar firma — aborta con 403 si falla
        $driver->verifyWebhook($request);

        // 2. Parsear mensaje — retorna null si es un status update u otro evento
        $message = $driver->parseIncomingMessage($request);

        if ($message === null) {
            return response('', 200);
        }

        // 3. Idempotencia — ignorar si ya procesamos este MessageSid
        if (WhatsappProcessedMessage::where('message_id', $message->messageId)->exists()) {
            return response('', 200);
        }

        // 4. Encolar el job
        ProcessIncomingWhatsappMessage::dispatch($message);

        // 5. Responder 200 vacío inmediatamente (no TwiML con texto)
        return response('', 200);
    }
}
```

**Por qué no TwiML para la respuesta:** El agente puede tardar más de los 15 segundos que Twilio espera para el TwiML síncrono. Con el job asíncrono + REST API el timeout no es problema.

---

### Job de procesamiento

```php
// app/Jobs/ProcessIncomingWhatsappMessage.php

class ProcessIncomingWhatsappMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120; // 2 minutos máximo

    public function __construct(
        private WhatsappIncomingMessage $message,
    ) {}

    public function handle(
        WhatsappDriver $driver,
        WhatsappMessageProcessor $processor,
    ): void {
        // Registrar mensaje como procesado (idempotencia)
        WhatsappProcessedMessage::create(['message_id' => $this->message->messageId]);

        // Procesar: iniciar conversación si es necesario + enviar al agente
        $result = $processor->process($this->message);

        // Enviar respuesta de texto
        $driver->sendTextMessage($this->message->phone, $result->reply);

        // Enviar adjuntos (videos de productos)
        foreach ($result->attachments as $attachment) {
            if ($attachment['type'] === 'video') {
                $driver->sendMediaMessage(
                    to: $this->message->phone,
                    mediaUrl: $attachment['url'],
                    caption: $attachment['product_name'] ?? '',
                );
            }
        }
    }

    public function failed(\Throwable $e): void
    {
        // Log del error; opcionalmente enviar mensaje de disculpa al cliente
        Log::error('WhatsApp message processing failed', [
            'phone'      => $this->message->phone,
            'messageId'  => $this->message->messageId,
            'error'      => $e->getMessage(),
        ]);
    }
}
```

---

### WhatsappMessageProcessor

Este es el componente **compartido** que reutiliza las actions existentes. No sabe nada de Twilio ni Meta.

```php
// app/Whatsapp/WhatsappMessageProcessor.php

class WhatsappMessageProcessor
{
    public function __construct(
        private IniciarChatbot $iniciarChatbot,
        private SendChatbotMessage $sendChatbotMessage,
    ) {}

    public function process(WhatsappIncomingMessage $message): WhatsappProcessingResult
    {
        // La empresa se resuelve internamente en cada action via ChatbotCompany::findOrFail().
        // Por ahora hay una sola empresa; en el futuro se resolverá por el número de destino
        // del webhook (campo `To` de Twilio / `to` de Meta), que identificará a qué empresa
        // pertenece ese número de WhatsApp Business.

        // 1. Iniciar o retomar conversación activa
        $chatbot = $this->iniciarChatbot->execute(
            phone: $message->phone,
            source: ChatbotSource::Whatsapp,
        );

        // 2. Enviar el mensaje al agente y obtener respuesta
        // SendChatbotMessage también resuelve la empresa internamente
        $response = $this->sendChatbotMessage->execute(
            conversationId: $chatbot['conversation_id'],
            source: ChatbotSource::Whatsapp,
            message: $message->body,
        );

        return new WhatsappProcessingResult(
            reply: $response['reply'],
            attachments: $response['attachments'],
        );
    }
}
```

#### WhatsappProcessingResult (DTO)

```php
readonly class WhatsappProcessingResult
{
    public function __construct(
        public string $reply,
        public array $attachments, // mismo formato que SendChatbotMessage retorna
    ) {}
}
```

---

### Adaptación de las Actions existentes

**No se requiere refactor.** Las actions ya tienen firmas limpias que aceptan parámetros directos:

```php
// IniciarChatbot — ya existe así:
public function execute(string $phone, ChatbotSource $source): array

// SendChatbotMessage — ya existe así:
public function execute(
    string $conversationId,
    ChatbotSource $source,
    string $message,
    ?array $productContext = null,
): array
```

Ninguna recibe un `Request`; ambas resuelven la empresa internamente via `ChatbotCompany::findOrFail()` (que busca por nombre en BD). El `WhatsappMessageProcessor` puede llamarlas directamente sin ningún cambio.

---

### Tabla de idempotencia

Para evitar procesar el mismo mensaje dos veces cuando Twilio reintenta el webhook:

```
tabla: whatsapp_processed_messages

- id:          uuid, PK
- message_id:  string(64), NOT NULL, UNIQUE  — MessageSid de Twilio / WAMID de Meta
- created_at:  timestamp
```

El registro se crea **al inicio del job** (no en el controller) para que si el job falla, se reintente correctamente la segunda vez. Si ya existe, el job finaliza sin hacer nada.

> Alternativa: usar la tabla para expirar registros viejos (ej. > 24 horas) con un scheduled command.

---

### Configuración de credenciales y selección de proveedor activo

Las credenciales **no se configuran con variables de entorno**: se ingresan desde la UI en `/configuration/whatsapp-providers` y se almacenan cifradas. El proveedor activo también se elige en esa misma pantalla.

#### Flujo completo

```
UI /configuration/whatsapp-providers
    │
    ├─ guarda credenciales (encrypted) ──────────────────────────────────────────┐
    │  configuration_whatsapp_provider_credentials                                │
    │                                                                             │
    └─ guarda proveedor por defecto ─────────────────────────────────────────────┤
       configuration_whatsapp_settings.default_provider                          │
                                                                                  │
WhatsappConfigurationBridge::apply()  (llamado en AppServiceProvider::boot())    │
    │                                                                             │
    ├─ lee credenciales de BD ◄───────────────────────────────────────────────────┘
    │  config('whatsapp.twilio.account_sid') = ...
    │  config('whatsapp.twilio.auth_token')  = ...
    │  config('whatsapp.twilio.from_number') = ...
    │
    └─ llama resolveDefaultProvider(WhatsappSetting::instance()->default_provider)
           │
           │  Prioridad de resolución:
           │  1. WhatsappSetting.default_provider  (si ese proveedor tiene credenciales listas)
           │  2. config('whatsapp.driver')          (env var, si ese proveedor está listo)
           │  3. Primer proveedor con credenciales listas
           │
           └─ setea config('whatsapp.driver') = 'twilio' | 'meta' | ...
                          │
                          ▼
       AppServiceProvider binding de WhatsappDriver
           lee config('whatsapp.driver') → instancia el driver correcto
           lee config('whatsapp.{driver}.*') → pasa credenciales al driver
```

#### Qué existe en el proyecto

Los campos de cada proveedor ya están definidos en `config/whatsapp-providers-admin.php` (Twilio y Meta). La tabla `configuration_whatsapp_settings` con `default_provider` y `WhatsappSetting::instance()` también ya existen.

#### Binding en AppServiceProvider

```php
$this->app->bind(WhatsappDriver::class, function () {
    // config('whatsapp.driver') ya fue resuelto por WhatsappConfigurationBridge::apply()
    // al proveedor activo seleccionado en la UI (o al primero disponible como fallback)
    $driver = config('whatsapp.driver');

    return match ($driver) {
        'twilio' => new TwilioWhatsappDriver(
            accountSid: config('whatsapp.twilio.account_sid'),
            authToken:  config('whatsapp.twilio.auth_token'),
            fromNumber: config('whatsapp.twilio.from_number'),
        ),
        // 'meta' => new MetaWhatsappDriver(...), // futuro
        default => throw new \InvalidArgumentException("Driver WhatsApp desconocido: {$driver}"),
    };
});
```

#### Env var opcional

```
WHATSAPP_DRIVER=twilio
```

Solo actúa como **segundo fallback** si `WhatsappSetting.default_provider` no está configurado y ese proveedor tiene sus credenciales listas. En producción normal, el proveedor activo se controla desde la UI y esta variable no es necesaria.

---

### URL de webhook en la pantalla de configuración

La pantalla `/configuration/whatsapp-providers` debe mostrar, por cada proveedor, la **URL del webhook** que el administrador necesita copiar y pegar en el panel del proveedor (Twilio Console, Meta Developer App, etc.).

#### Dónde se define la URL por proveedor

Agregar la clave `webhook_route` en `config/whatsapp-providers-admin.php`:

```php
'twilio' => [
    'label'          => 'Twilio',
    'webhook_route'  => 'webhook.whatsapp.twilio',   // nombre de la ruta Laravel
    'fields'         => [ ... ],
],
'meta' => [
    'label'          => 'Meta',
    'webhook_route'  => 'webhook.whatsapp.meta',     // futuro
    'fields'         => [ ... ],
],
```

#### Cómo llega al frontend

`WhatsappConfigurationBridge::providersForFrontend()` ya construye el array por proveedor. Agregar `webhookUrl` resolviendo la ruta nombrada:

```php
// En el array que retorna providersForFrontend(), agregar:
'webhookUrl' => Route::has($definition['webhook_route'] ?? '')
    ? route($definition['webhook_route'])
    : null,
```

El controlador `WhatsappProvidersController::edit()` ya pasa `providers` al frontend; este campo llega automáticamente.

#### Qué muestra el frontend

En la página `resources/js/pages/configuration/whatsapp-providers/edit.tsx`, para cada proveedor que tenga `webhookUrl`, mostrar un campo de solo lectura con el título **"URL de callback"** y un botón de copiar. Ejemplo de texto de ayuda:

> Copia esta URL y pégala en el campo **"Webhook URL"** de tu cuenta de Twilio → WhatsApp → Sandbox/Number.

---

### Rutas

```php
// routes/webhooks.php (nuevo archivo, sin CSRF, sin auth)

Route::post('/webhook/whatsapp/twilio', TwilioWhatsappController::class)
    ->name('webhook.whatsapp.twilio');

// Futuro Meta:
// Route::get('/webhook/whatsapp/meta', [MetaWhatsappController::class, 'verify']);
// Route::post('/webhook/whatsapp/meta', [MetaWhatsappController::class, 'handle']);
```

Excluir del CSRF en `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'webhook/whatsapp/*',
    ]);
})
```

Registrar el archivo de rutas en `bootstrap/app.php`:

```php
->withRouting(function () {
    Route::middleware('api')
        ->group(base_path('routes/webhooks.php'));
    // ...
})
```

---

### Dependencia Twilio SDK

```bash
composer require twilio/sdk
```

---

### Modelo de datos

#### `whatsapp_processed_messages`

```
- id:          uuid, PK
- message_id:  string(64), NOT NULL, UNIQUE
- created_at:  timestamp (sin updated_at)
```

No se necesitan tablas adicionales; las conversaciones y mensajes se almacenan en `chatbot_conversations` y `chatbot_messages` como los demás canales.

---

### Consideraciones técnicas

#### Cola de procesamiento

- El job debe correr en una **cola dedicada** (`whatsapp`) para no bloquear otras colas en producción.
- En el `.env` de producción: `QUEUE_CONNECTION=redis` (o SQS).
- Configurar el worker: `php artisan queue:work --queue=whatsapp,default`.

#### Validación de firma en entorno local/staging

- Twilio no puede hacer webhooks a `localhost`. Usar [ngrok](https://ngrok.com) o [Expose](https://expose.dev) durante desarrollo.
- En tests, mockear el driver (`WhatsappDriver` se puede falsificar sin llamar a Twilio).
- Para desactivar la validación en staging si es necesario, usar una variable `WHATSAPP_VERIFY_WEBHOOK=false` —solo para staging, nunca producción.

#### Mensajes sin texto

- Si el cliente envía solo una imagen o audio, Twilio envía `Body` vacío y `MediaUrl0` con el adjunto.
- En esta entrega: si `Body` está vacío, responder con un mensaje fijo: _"Por el momento solo puedo atender mensajes de texto. Por favor escríbeme tu consulta."_

#### Timeout y mensaje de espera

- Si el agente no responde en 60 segundos, enviar al cliente: _"Estoy procesando tu consulta, te respondo en un momento."_
- Implementar con `dispatch(...)->delay()` + un segundo job de notificación, o con `onProcessing` callback (opcional, no obligatorio en esta entrega).

#### Normalización de teléfono

- Usar la misma lógica de `ChatbotPhone` que ya existe en el proyecto para garantizar que el número de WhatsApp se normalice igual que en el canal web.
- Twilio envía `whatsapp:+56912345678`; después de strip del prefijo queda `+56912345678`. Este formato debe ser consistente con el que usa el canal web.

#### Sandbox de Twilio (desarrollo)

- Twilio ofrece un sandbox de WhatsApp gratuito para pruebas. El número es `+14155238886` y los clientes deben enviar un código de activación.
- Para producción se necesita un número de WhatsApp Business aprobado.

#### Seguridad

- **Siempre** validar la firma de Twilio. No omitir en producción.
- El endpoint no requiere autenticación propia del sistema (es público para Twilio), por eso la firma es la única barrera.
- Registrar intentos con firma inválida en el log.

---

### Archivos involucrados

**Archivos a crear:**

| Capa               | Ruta                                                                                 |
| ------------------ | ------------------------------------------------------------------------------------ |
| Rutas              | `routes/webhooks.php`                                                                |
| Contrato           | `app/Contracts/WhatsappDriver.php`                                                   |
| DTO entrada        | `app/Whatsapp/WhatsappIncomingMessage.php`                                           |
| DTO salida         | `app/Whatsapp/WhatsappProcessingResult.php`                                          |
| Driver Twilio      | `app/Whatsapp/Drivers/TwilioWhatsappDriver.php`                                     |
| Procesador         | `app/Whatsapp/WhatsappMessageProcessor.php`                                          |
| Controlador webhook| `app/Http/Controllers/Webhooks/TwilioWhatsappController.php`                        |
| Job                | `app/Jobs/ProcessIncomingWhatsappMessage.php`                                        |
| Modelo idempotencia| `app/Models/Whatsapp/WhatsappProcessedMessage.php`                                  |
| Migración          | `database/migrations/YYYY_MM_DD_create_whatsapp_processed_messages_table.php`       |
| Dependencia        | `composer require twilio/sdk`                                                        |

**Archivos a modificar:**

| Capa               | Ruta                                                                                 | Cambio                                                      |
| ------------------ | ------------------------------------------------------------------------------------ | ----------------------------------------------------------- |
| Config             | `config/whatsapp-providers-admin.php`                                               | Agregar `webhook_route` por proveedor                       |
| Bridge             | `app/Support/WhatsappConfigurationBridge.php`                                       | Agregar `webhookUrl` en `providersForFrontend()`            |
| Service Provider   | `app/Providers/AppServiceProvider.php`                                              | Agregar binding de `WhatsappDriver`                         |
| Frontend config    | `resources/js/pages/configuration/whatsapp-providers/edit.tsx`                     | Mostrar "URL de callback" con botón copiar por proveedor    |
| Bootstrap          | `bootstrap/app.php`                                                                 | Excluir `webhook/whatsapp/*` del CSRF; registrar rutas      |

**Archivos existentes que se usan sin cambios:**

| Capa               | Ruta                                                                                 |
| ------------------ | ------------------------------------------------------------------------------------ |
| Config             | `config/whatsapp.php`                                                                |
| Config admin       | `config/whatsapp-providers-admin.php` (ya tiene Twilio y Meta definidos)            |
| Tabla credenciales | `configuration_whatsapp_provider_credentials`                                       |
| Modelo             | `app/Models/Configuration/WhatsappProviderCredential.php`                           |
| Bridge             | `app/Support/WhatsappConfigurationBridge.php` — `apply()` ya está en AppServiceProvider |
| Controlador config | `app/Http/Controllers/Configuration/WhatsappProvidersController.php`                |
| Actions chatbot    | `app/Actions/Public/IniciarChatbot.php` / `SendChatbotMessage.php` — sin cambios   |

---

### Resolución de empresa por número de destino (futuro)

Cuando el sistema soporte múltiples empresas con distintos números de WhatsApp, la empresa se resolverá así:

1. El `WhatsappIncomingMessage` ya incluye `toNumber` (el número de la empresa que recibió el mensaje).
2. Se creará una tabla de configuración que mapea número de teléfono → empresa (ej. `company_whatsapp_numbers`).
3. El `WhatsappMessageProcessor` resolverá la empresa por `toNumber` en lugar de usar `ChatbotCompany::findOrFail()`.
4. Las actions `IniciarChatbot` y `SendChatbotMessage` deberán aceptar `company` como parámetro (o el `ChatbotCompany` deberá resolverse desde un contexto inyectable).

Por ahora `ChatbotCompany::findOrFail()` dentro de cada action es suficiente.

---

### Hoja de ruta para migrar a Meta en el futuro

Cuando se quiera agregar la **API de Meta (WhatsApp Business API directa)**:

1. Crear `app/Whatsapp/Drivers/MetaWhatsappDriver.php` implementando `WhatsappDriver`.
2. Crear `app/Http/Controllers/Webhooks/MetaWhatsappController.php` con los métodos `verify` (GET, challenge de Meta) y `handle` (POST).
3. Agregar las rutas en `routes/webhooks.php`.
4. Agregar el caso `'meta'` en el binding del `AppServiceProvider`.
5. Configurar `WHATSAPP_DRIVER=meta` y las variables de Meta en `.env`.
6. **No tocar**: `WhatsappMessageProcessor`, `ProcessIncomingWhatsappMessage`, `IniciarChatbot`, `SendChatbotMessage`.

La capa de procesamiento es **100% reutilizable**; solo cambia el driver.

---

### Pruebas

**`tests/Feature/Webhooks/TwilioWhatsappWebhookTest.php`:**

- Request válido con firma correcta → encola `ProcessIncomingWhatsappMessage`, retorna 200.
- Request con firma inválida → retorna 403, no encola job.
- Request de status update (sin `Body`) → retorna 200 sin encolar job.
- `MessageSid` ya procesado → retorna 200 sin encolar job duplicado.
- Cuerpo vacío (solo media) → encola job; el job envía mensaje de "solo texto por favor".

**`tests/Unit/Whatsapp/TwilioWhatsappDriverTest.php`:**

- `parseIncomingMessage` extrae phone sin prefijo `whatsapp:`, body y messageId.
- `parseIncomingMessage` retorna null cuando no hay `Body` en el request.
- `sendTextMessage` llama al cliente de Twilio con el formato correcto.
- `sendTextMessage` divide mensajes largos en chunks de ≤ 4096 caracteres.

**`tests/Feature/Whatsapp/WhatsappMessageProcessorTest.php`:**

- Teléfono sin conversación activa → llama `IniciarChatbot` + `SendChatbotMessage`, retorna reply + attachments.
- Teléfono con conversación activa → no crea nueva conversación, agrega mensaje a la existente.
- Los mensajes se registran con `source: whatsapp`.

> En los tests del procesador y del job, usar `WhatsappDriver` falso (fake) que no llama a Twilio.
