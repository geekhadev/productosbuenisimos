---
description: Chatbot de ventas omnicanal; el visitante se identifica con su teléfono, puede tener múltiples conversaciones (una activa), mensajes en tabla propia con origen por canal
type: técnico
date: 2026-05-18
status: draft
user: Irwing Naranjo
---

# Landing: Chatbot de Ventas

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

La tienda pública (landing) ofrece un **chatbot de ventas flotante** accesible desde cualquier página pública (`/` y `/productos/{id}`). Antes de chatear, el visitante ingresa su **número de teléfono**, que lo identifica en el sistema.

El diseño es **omnicanal**: un mismo teléfono puede participar en la misma conversación activa desde distintos canales (Web, WhatsApp, Facebook, Instagram). Cada conversación y cada mensaje registra su **origen**, permitiendo saber por dónde llegó el cliente en cada momento.

Un teléfono puede tener **múltiples conversaciones** a lo largo del tiempo, pero solo **una activa** en un momento dado. La conversación activa persiste entre visitas y entre canales: al regresar con el mismo teléfono desde cualquier canal, el visitante retoma el hilo donde quedó.

El chatbot conecta al visitante con el **SalesAgent** para guiarlo por el proceso completo de compra.

### Actores y roles

| Actor               | Acceso / comportamiento                                                                                            |
| ------------------- | ------------------------------------------------------------------------------------------------------------------ |
| Visitante (público) | Ingresa su teléfono para iniciar o retomar su conversación activa; puede hacerlo desde cualquier canal soportado. |
| SalesAgent (IA)     | Conduce la conversación con los tools de ventas; recibe `company_id` de PRODUCTOS BUENISIMOS SPA.                 |

### Canales soportados

| Valor (`source`) | Descripción                              |
| ---------------- | ---------------------------------------- |
| `web`            | Chatbot flotante en la tienda pública    |
| `whatsapp`       | Integración con WhatsApp Business API    |
| `facebook`       | Messenger de página de Facebook          |
| `instagram`      | Direct Messages de Instagram             |

> En esta entrega solo se implementa el canal `web`. Los demás canales son integraciones futuras; el modelo de datos los soporta desde el inicio.

### Flujo principal (canal web)

1. El visitante hace clic en el botón flotante del chatbot.
2. **Si es su primera visita** (sin teléfono en `localStorage`): ve un formulario con campo de teléfono y botón "Iniciar chat".
3. Ingresa su número → el sistema resuelve o crea su conversación activa, registrando `source: web`.
4. Se abre el panel de chat con el **historial de la conversación activa** (si existía, de cualquier canal).
5. **Si es conversación nueva y está en detalle de producto**: el primer mensaje lleva el contexto del producto inyectado automáticamente.
6. El visitante conversa con el agente; cada mensaje queda registrado con `source: web`.
7. **En visitas posteriores**: el teléfono se recuerda en `localStorage`; el panel se abre directamente con la conversación activa.
8. El visitante puede iniciar una nueva conversación desde el panel (la actual pasa a inactiva).
9. Puede limpiar su teléfono guardado ("No soy yo") para que otro visitante use el dispositivo.

### Reglas de negocio

- Un teléfono puede tener muchas conversaciones, pero solo **una con `is_active = true`** por empresa.
- Al iniciar una nueva conversación, la anterior se marca `is_active = false`.
- La **conversación** registra el canal en que fue **creada** (`source`); puede recibir mensajes de otros canales después.
- Cada **mensaje** registra su propio `source` — el canal por el que llegó ese mensaje específico.
- El historial unificado muestra mensajes de todos los canales en orden cronológico.
- El teléfono se normaliza en el servidor antes de usarlo como clave.
- La empresa es **siempre PRODUCTOS BUENISIMOS SPA**, resuelta en el servidor; nunca configurable por el visitante.
- El **contexto de producto** se inyecta solo en el **primer mensaje de una conversación nueva** (cuando no hay historial previo).

---

## ESPECIFICACIÓN TÉCNICA

### Modelo de datos

Se crean **dos tablas propias** del chatbot. Las tablas del AI SDK (`agent_conversations`, `agent_conversation_messages`) siguen existiendo para que el agente gestione su contexto interno, pero la **UI del chatbot lee sus propias tablas**.

El campo `source` usa el tipo `enum` con los valores: `web`, `whatsapp`, `facebook`, `instagram`.

#### `chatbot_conversations`

```
- id:                    uuid, PK
- company_id:            uuid, NOT NULL, FK → configuration_companies
- phone:                 string(30), NOT NULL (normalizado)
- agent_conversation_id: string(36), NOT NULL, FK → agent_conversations
- source:                enum('web','whatsapp','facebook','instagram'), NOT NULL
                         — canal en que fue CREADA la conversación
- is_active:             boolean, NOT NULL, default: true
- created_at / updated_at: timestamps

Índices:
- INDEX (company_id, phone)        — para lookup por teléfono
- UNIQUE (agent_conversation_id)   — un agente-conv por chat-conv
```

> No hay UNIQUE en `(company_id, phone)` porque un teléfono puede tener múltiples filas (conversaciones distintas). La unicidad de la activa se garantiza en la lógica de la Action (desactivar la anterior antes de crear una nueva).

#### `chatbot_messages`

```
- id:                      uuid, PK
- chatbot_conversation_id: uuid, NOT NULL, FK → chatbot_conversations
- role:                    enum('user', 'assistant'), NOT NULL
- source:                  enum('web','whatsapp','facebook','instagram'), NOT NULL
                           — canal por el que llegó/se envió ESTE mensaje
- content:                 text, NOT NULL   — solo el texto visible al visitante
- created_at:              timestamp

Índice:
- INDEX (chatbot_conversation_id, created_at)
```

> La separación entre `chatbot_messages` (UI) y `agent_conversation_messages` (SDK interno) evita exponer tool calls, usage stats y metadatos del agente al frontend.
>
> El `source` en el mensaje permite mostrar en el historial una etiqueta de canal ("vía WhatsApp", "vía Web") y permite filtrar o auditar mensajes por canal en el futuro.

### Flujo de inicialización

```
POST /chatbot/iniciar
```

**Request:**
```json
{
  "phone": "+56912345678",
  "source": "web"
}
```

**Lógica (`IniciarChatbot` action):**
1. Normalizar el teléfono (solo dígitos, sin código de país opcional — definir convención).
2. Resolver `company_id` de PRODUCTOS BUENISIMOS SPA.
3. Buscar `chatbot_conversations` con `(company_id, phone, is_active = true)`.
4. **Si existe**: devolver `conversation_id` + historial de `chatbot_messages` (todos los mensajes, independiente de su `source`).
5. **Si no existe**: crear conversación en el AI SDK → insertar `chatbot_conversation` con `source` del request (`is_active = true`) → devolver `conversation_id` vacío con `is_new: true`.

**Response (éxito):**
```json
{
  "conversation_id": "uuid de chatbot_conversations",
  "is_new": false,
  "messages": [
    { "role": "user" | "assistant", "source": "web" | "whatsapp" | ..., "content": "...", "created_at": "ISO8601" }
  ]
}
```

### Inicio de conversación nueva (desde el panel)

```
POST /chatbot/nueva-conversacion
```

**Request:**
```json
{ "conversation_id": "uuid de chatbot_conversations activa actual" }
```

**Lógica:**
1. Validar que `conversation_id` existe y pertenece a PRODUCTOS BUENISIMOS SPA.
2. Marcar esa conversación como `is_active = false`.
3. Crear nueva conversación en el AI SDK.
4. Insertar nuevo `chatbot_conversation` con `is_active = true`, mismo `phone`.
5. Devolver el nuevo `conversation_id` e `is_new: true`.

**Response:**
```json
{
  "conversation_id": "uuid nuevo",
  "is_new": true,
  "messages": []
}
```

### Envío de mensajes

```
POST /chatbot/mensaje
```

**Request:**
```json
{
  "conversation_id": "uuid de chatbot_conversations",
  "source": "web",
  "message": "string",
  "product_context": {
    "id": "uuid",
    "name": "string",
    "code": "string",
    "sku": "string",
    "price": 9990
  }
}
```

**Lógica (`SendChatbotMessage` action):**
1. Resolver `chatbot_conversation` por `conversation_id`; validar que pertenece a PRODUCTOS BUENISIMOS SPA y que `is_active = true`.
2. Construir el prompt: si viene `product_context`, anteponer bloque de contexto.
3. Guardar mensaje del visitante en `chatbot_messages` (role: `user`, `source` del request, content: mensaje original sin el contexto inyectado).
4. Llamar al agente: `SalesAgent::make(companyId: ...)->continue($agentConversationId)->prompt($builtPrompt)`.
5. Guardar respuesta en `chatbot_messages` (role: `assistant`, `source` del request, content: texto de la respuesta).
6. Devolver la respuesta.

**Response (éxito):**
```json
{ "reply": "Texto de respuesta del agente" }
```

### Inyección de contexto de producto

El controlador antepone al mensaje del usuario un bloque invisible para el visitante:

```
[Contexto del producto consultado]
- Nombre: Nombre del Producto
- Código: PROD-001
- SKU: SKU-001
- Precio: $9.990

Mensaje del visitante: Hola, quiero hacer un pedido
```

El texto combinado va al agente; en `chatbot_messages` se guarda solo el mensaje original del visitante.

### Validaciones

**`POST /chatbot/iniciar`:**

| Campo    | Reglas                                                                          |
| -------- | ------------------------------------------------------------------------------- |
| `phone`  | `required`, `string`, `min:7`, `max:20`                                        |
| `source` | `required`, `string`, `in:web,whatsapp,facebook,instagram`                     |

**`POST /chatbot/nueva-conversacion`:**

| Campo             | Reglas                                                                         |
| ----------------- | ------------------------------------------------------------------------------ |
| `conversation_id` | `required`, `uuid`, debe existir en `chatbot_conversations` y ser activa      |
| `source`          | `required`, `string`, `in:web,whatsapp,facebook,instagram`                    |

**`POST /chatbot/mensaje`:**

| Campo                   | Reglas                                                                              |
| ----------------------- | ----------------------------------------------------------------------------------- |
| `conversation_id`       | `required`, `uuid`, debe existir en `chatbot_conversations` con `is_active = true` |
| `source`                | `required`, `string`, `in:web,whatsapp,facebook,instagram`                         |
| `message`               | `required`, `string`, `max:1000`                                                   |
| `product_context`       | `nullable`, `array`                                                                 |
| `product_context.id`    | `required_with:product_context`, `uuid`                                            |
| `product_context.name`  | `required_with:product_context`, `string`, `max:255`                               |
| `product_context.code`  | `required_with:product_context`, `string`, `max:100`                               |
| `product_context.sku`   | `required_with:product_context`, `string`, `max:100`                               |
| `product_context.price` | `required_with:product_context`, `numeric`, `min:0`                                |

### Resolución de empresa

```php
$company = Company::where('name', 'PRODUCTOS BUENISIMOS SPA')->firstOrFail();
```

Hardcodeado por nombre. En el futuro se resolverá por dominio u otra configuración.

### Throttle

- `POST /chatbot/iniciar`: **10 intentos por minuto por IP**.
- `POST /chatbot/nueva-conversacion`: **5 por minuto por IP**.
- `POST /chatbot/mensaje`: **20 mensajes por minuto por IP**.

### Archivos involucrados

| Capa              | Ruta                                                                                      |
| ----------------- | ----------------------------------------------------------------------------------------- |
| Migración         | `database/migrations/YYYY_MM_DD_create_chatbot_conversations_table.php`                   |
| Migración         | `database/migrations/YYYY_MM_DD_create_chatbot_messages_table.php`                        |
| Modelo            | `app/Models/Public/ChatbotConversation.php`                                               |
| Modelo            | `app/Models/Public/ChatbotMessage.php`                                                    |
| Rutas             | `routes/public.php` — tres rutas POST                                                     |
| Controlador       | `app/Http/Controllers/Public/ChatbotController.php` — métodos `iniciar`, `nuevaConversacion`, `mensaje` |
| Form Request      | `app/Http/Requests/Public/IniciarChatbotRequest.php`                                      |
| Form Request      | `app/Http/Requests/Public/NuevaConversacionChatbotRequest.php`                            |
| Form Request      | `app/Http/Requests/Public/ChatbotMessageRequest.php`                                      |
| Action            | `app/Actions/Public/IniciarChatbot.php`                                                   |
| Action            | `app/Actions/Public/NuevaConversacionChatbot.php`                                         |
| Action            | `app/Actions/Public/SendChatbotMessage.php`                                               |
| Widget React      | `resources/js/components/chatbot/chatbot-widget.tsx`                                      |
| Formulario tel.   | `resources/js/components/chatbot/chatbot-phone-form.tsx`                                  |
| Panel React       | `resources/js/components/chatbot/chatbot-panel.tsx`                                       |
| Hook React        | `resources/js/components/chatbot/use-chatbot.ts`                                          |
| Tipos TS          | `resources/js/components/chatbot/types.ts`                                                |
| Layout landing    | `resources/js/pages/landing/index.tsx` — `<ChatbotWidget />`                             |
| Detalle producto  | `resources/js/pages/landing/product-show.tsx` — `<ChatbotWidget product={product} />`    |

### Componente React — estructura

**`chatbot-widget.tsx`**
- Botón flotante circular (esquina inferior derecha, `z-50`).
- Al montar, lee `chatbot_phone` y `chatbot_conversation_id` de `localStorage`.
- Si hay datos guardados → llama automáticamente a `POST /chatbot/iniciar` para recuperar la conversación activa.
- Si no hay → al hacer clic muestra `ChatbotPhoneForm`.
- Acepta prop opcional `product?: { id, name, code, sku, price }`.

**`chatbot-phone-form.tsx`**
- Campo de teléfono + botón "Iniciar chat".
- Al enviar, llama a `POST /chatbot/iniciar`.
- En éxito: guarda `phone` y `conversation_id` en `localStorage`.

**`chatbot-panel.tsx`**
- Panel lateral o drawer (~360 px desktop, full-width móvil).
- Renderiza historial de `chatbot_messages` al abrir.
- Burbujas diferenciadas (visitante vs agente).
- Input de texto + botón enviar.
- Estado de carga ("El agente está escribiendo…").
- Scroll automático al último mensaje.
- Botón "Nueva conversación" → llama a `POST /chatbot/nueva-conversacion`, actualiza `conversation_id` en `localStorage` y limpia los mensajes del panel.
- Botón "No soy yo" → limpia `localStorage`, vuelve a `ChatbotPhoneForm`.

**`use-chatbot.ts`**
- Estado: `phase: 'phone' | 'loading' | 'chat'`, `messages`, `conversationId`, `isNew`, `isSending`, `error`.
- El hook conoce el `source` constante `'web'`; lo envía en todos los llamados al backend.
- `iniciar(phone)`: llama a `/iniciar` con `source: 'web'`, popula estado (incluyendo `source` de cada mensaje en el historial), guarda en `localStorage`.
- `enviar(message)`:
  1. Agrega mensaje del visitante a `messages` con `source: 'web'` (optimista).
  2. Incluye `product_context` solo si `isNew && product != null`; limpia `isNew` tras el primer envío.
  3. Llama a `/chatbot/mensaje` con `source: 'web'`, agrega respuesta del agente con `source: 'web'`.
- `nuevaConversacion()`: llama a `/chatbot/nueva-conversacion` con `source: 'web'`, actualiza `conversationId` en estado y `localStorage`, limpia `messages`, establece `isNew: true`.
- `cerrarSesion()`: borra `localStorage`, resetea estado a `phase: 'phone'`.

### Integración en páginas landing

**`index.tsx`:**
```tsx
<ChatbotWidget />
```

**`product-show.tsx`:**
```tsx
<ChatbotWidget
  product={{ id: product.id, name: product.name, code: product.code, sku: product.sku, price: Number(product.price) }}
/>
```

### Elementos de frontend

- [x] Formulario de teléfono como pantalla inicial (si no hay sesión guardada)
- [x] Botón flotante visible en todas las páginas públicas
- [x] Carga automática de conversación activa desde `localStorage`
- [x] Historial de mensajes renderizado desde `chatbot_messages` al abrir
- [x] Panel de chat con burbujas diferenciadas (visitante / agente)
- [x] Estado de carga ("El agente está escribiendo…")
- [x] Contexto de producto inyectado en primer mensaje de conversación nueva
- [x] Botón "Nueva conversación" para reiniciar el hilo
- [x] Botón "No soy yo" para cambiar de teléfono
- [x] Manejo de error de red con mensaje amigable
- [x] Diseño responsive: panel full-width en móvil, drawer fijo en desktop
- [x] Etiqueta de canal (`source`) visible opcionalmente en cada mensaje del historial (ej. ícono o badge "vía WhatsApp")
- [ ] Listado de conversaciones anteriores (inactivas) — No aplica en esta entrega
- [ ] Verificación de teléfono por OTP — No aplica en esta entrega
- [ ] Integración con WhatsApp / Facebook / Instagram — No aplica en esta entrega (el modelo de datos los soporta)

### Consideraciones técnicas

- **`source` como constante en el canal web:** el frontend siempre envía `source: 'web'`. Los canales futuros (WhatsApp, etc.) lo enviarán desde sus propios adaptadores/webhooks. El valor nunca debe ser configurable por el usuario final.
- **Seguridad de `conversation_id`:** el backend valida que `conversation_id` pertenece a PRODUCTOS BUENISIMOS SPA y que `is_active = true`. Impide reutilizar IDs ajenos o conversaciones inactivas.
- **Unicidad de la conversación activa:** la Action `NuevaConversacionChatbot` marca la anterior como `is_active = false` dentro de una transacción antes de crear la nueva. No hay UNIQUE en BD para esto (ya que un teléfono tiene múltiples filas), así que la transacción es la garantía.
- **Mensajes duplicados:** `chatbot_messages` guarda solo el contenido visible (sin tool calls ni metadatos internos del agente). El SDK guarda su propia versión completa en `agent_conversation_messages` para el contexto del agente.
- **Normalización de teléfono:** definir convención única en la Action `IniciarChatbot` (ej. solo dígitos, eliminar `+56` si Chile). La misma lógica aplica siempre para garantizar que `+569...` y `09...` sean el mismo visitante si se decide así.
- **Historial en `/iniciar`:** leer `chatbot_messages` ordenados por `created_at ASC` para la conversación activa; devolver al frontend solo `role`, `content` y `created_at`.
- **Sin auth:** rutas públicas. `company_id` siempre viene del servidor.
- **Wayfinder:** generar funciones tipadas para las tres rutas y usarlas en el hook.
- **CORS / CSRF:** confirmar que las rutas bajo `routes/public.php` manejan correctamente el token de Inertia/axios o están excluidas de CSRF.
- **Timeout del agente:** configurar `max_execution_time` adecuado; considerar respuesta asíncrona (jobs + polling o WebSockets) en el futuro si el agente tarda.

### Pruebas

**`tests/Feature/Public/ChatbotIniciarTest.php`:**
- Teléfono nuevo + `source: web` → crea `chatbot_conversation` con `source: web`, `is_active: true`, `is_new: true`, `messages: []`.
- Mismo teléfono → devuelve misma `conversation_id` + historial (con `source` de cada mensaje).
- Teléfono vacío → 422.
- `source` inválido → 422.
- Throttle → 429.

**`tests/Feature/Public/ChatbotNuevaConversacionTest.php`:**
- `conversation_id` activo → lo desactiva, crea nueva conversación con `source` del request, devuelve nuevo `conversation_id`.
- `conversation_id` inactivo → 422.
- `conversation_id` de otra empresa → 422.
- `source` inválido → 422.

**`tests/Feature/Public/ChatbotMensajeTest.php`:**
- Conversación activa + mensaje + `source: web` → devuelve `reply`; guarda en `chatbot_messages` con `source: web`.
- Conversación activa + mensaje + `product_context` → prompt al agente incluye bloque de contexto; `chatbot_messages` guarda solo el mensaje original.
- Mensajes de fuentes distintas en la misma conversación → historial devuelve `source` correcto por mensaje.
- `conversation_id` inactivo → 422.
- `conversation_id` de otra empresa → 422.
- `source` inválido → 422.
- `message` vacío → 422.
- `message` > 1000 caracteres → 422.
- `product_context` parcial → 422.
- Throttle → 429.
