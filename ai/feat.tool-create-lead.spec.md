---
description: Registro automático de lead al iniciar el chatbot (teléfono ingresado antes del chat); no es tool del agente
type: técnico
date: 2026-05-21
status: draft
user: Irwing Naranjo
---

# Lead al iniciar chatbot (servidor, no tool de IA)

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Al ingresar el teléfono (antes de escribir en el chat), el servidor registra o recupera el lead en `sales_leads`. No lo hace el agente de IA: ocurre en `IniciarChatbot` (teléfono nuevo o conversación activa existente) y en `NuevaConversacionChatbot` (nueva conversación con el mismo teléfono).

### Actores y roles

| Actor     | Acceso / comportamiento                                                                       |
| --------- | --------------------------------------------------------------------------------------------- |
| Servidor  | `EnsureLeadForPhoneAction` desde `IniciarChatbot` / `NuevaConversacionChatbot` con `company_id`, `source` del canal y teléfono normalizado |

### Flujo principal

1. El visitante ingresa su teléfono y el frontend llama a `POST /chatbot/iniciar` (o retoma conversación activa).
2. La Action del chatbot normaliza el teléfono y llama a `EnsureLeadForPhoneAction`.
3. La action busca si existe un lead activo (no eliminado) con ese teléfono en la empresa.
4. Si **no existe**: crea el lead con `status = nuevo`.
   - Si el teléfono ya corresponde a un `Customer` activo de la empresa, asigna `customer_id` y `status = convertido`.
5. Si **ya existe**: lo devuelve sin crear duplicado.
6. Devuelve el lead como JSON.
7. El visitante puede escribir en el chat; el agente no registra el lead.

### Reglas de negocio

- El lead queda asociado a la empresa del `company_id` inyectado por el agente.
- **No se crean duplicados**: si ya existe un lead activo (sin `deleted_at`) con el mismo `phone` y `company_id`, se devuelve el existente.
- Si el teléfono coincide con un `Customer` activo de la empresa, el lead se crea con `customer_id` apuntando a ese customer y `status = convertido`.
- El `source` proviene del contexto del agente (determinado por el canal del chatbot: web, whatsapp, etc.); no es un parámetro del agente.
- Los leads con `deleted_at IS NOT NULL` no se consideran activos; se puede crear uno nuevo para el mismo teléfono.

### Datos que maneja el tool

| Campo      | Origen  | Obligatorio | Notas                                                       |
| ---------- | ------- | ----------- | ----------------------------------------------------------- |
| company_id | Agente  | Sí          | UUID de la empresa activa; inyectado en el constructor      |
| source     | Agente  | Sí          | Canal del chatbot; inyectado en el constructor              |
| phone      | Parámetro | Sí        | Teléfono del usuario que inició la conversación; máx. 40 caracteres |

---

## ESPECIFICACIÓN TÉCNICA

### Modelo de datos

```
tabla: sales_leads

Campos escritos por el tool:
- id:          uuid (generado automáticamente)
- company_id:  uuid (inyectado por el agente)
- phone:       string(40)
- source:      string  (inyectado por el agente)
- status:      string  — 'nuevo' por defecto; 'convertido' si hay customer match
- customer_id: uuid | null  — FK al customer si ya existe uno con ese teléfono
```

### Validaciones

| Campo   | Reglas                             |
| ------- | ---------------------------------- |
| phone   | obligatorio, string, max:40        |

### Archivos involucrados

| Capa   | Ruta                                               |
| ------ | -------------------------------------------------- |
| Action | `app/Actions/Sales/Leads/EnsureLeadForPhoneAction.php` |
| Trait  | `app/Actions/Public/Concerns/EnsuresChatbotLead.php`   |
| Chat   | `app/Actions/Public/IniciarChatbot.php`, `NuevaConversacionChatbot.php` |
| Modelo | `app/Models/Sales/Lead.php`, `Customer.php`        |

### Estructura del tool

```php
<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\CreateLeadAction;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Responses\ToolResponse;

class CreateLead implements Tool
{
    public function __construct(
        private readonly string $companyId,
        private readonly string $source,
    ) {}

    public function name(): string
    {
        return 'create_lead';
    }

    public function description(): string
    {
        return 'Registra al usuario como lead al inicio de la conversación. '
             . 'Si ya existe un lead activo con ese teléfono en la empresa, lo devuelve sin crear duplicado. '
             . 'Llama a este tool al inicio de cada conversación, antes de responder al usuario.';
    }

    public function parameters(): array
    {
        return [
            'phone' => [
                'type'        => 'string',
                'description' => 'Número de teléfono del usuario que inició la conversación.',
                'required'    => true,
            ],
        ];
    }

    public function handle(array $parameters): ToolResponse
    {
        $result = (new CreateLeadAction)->execute(
            companyId: $this->companyId,
            source: $this->source,
            phone: $parameters['phone'],
        );

        return ToolResponse::make(json_encode($result));
    }
}
```

### Estructura de la Action

```php
<?php

namespace App\Actions\AI\Tools;

use App\Models\Sales\Customer;
use App\Models\Sales\Lead;
use Illuminate\Support\Facades\Validator;

class CreateLeadAction
{
    public function execute(string $companyId, string $source, string $phone): array
    {
        $validator = Validator::make(['phone' => $phone], [
            'phone' => ['required', 'string', 'max:40'],
        ]);

        if ($validator->fails()) {
            return ['error' => $validator->errors()->toArray()];
        }

        // Devolver lead activo existente sin duplicar
        $existing = Lead::forCompany($companyId)
            ->where('phone', $phone)
            ->first();

        if ($existing !== null) {
            return $existing->load('customer')->toArray();
        }

        // Verificar si el teléfono ya corresponde a un customer
        $customer = Customer::forCompany($companyId)
            ->where('phone', $phone)
            ->first();

        $lead = Lead::create([
            'company_id'  => $companyId,
            'phone'       => $phone,
            'source'      => $source,
            'status'      => $customer ? 'convertido' : 'nuevo',
            'customer_id' => $customer?->id,
        ]);

        return $lead->load('customer')->toArray();
    }
}
```

### Respuesta esperada (JSON — lead nuevo)

```json
{
  "id": "uuid",
  "company_id": "uuid",
  "phone": "555-1234",
  "source": "web",
  "status": "nuevo",
  "customer_id": null,
  "customer": null,
  "created_at": "2026-05-21T12:00:00.000000Z",
  "updated_at": "2026-05-21T12:00:00.000000Z",
  "deleted_at": null
}
```

### Respuesta esperada (JSON — lead ya existente o convertido)

```json
{
  "id": "uuid",
  "company_id": "uuid",
  "phone": "555-1234",
  "source": "whatsapp",
  "status": "convertido",
  "customer_id": "uuid",
  "customer": {
    "id": "uuid",
    "full_name": "Juan Pérez",
    "phone": "555-1234"
  },
  "created_at": "2026-05-21T10:00:00.000000Z",
  "updated_at": "2026-05-21T10:00:00.000000Z",
  "deleted_at": null
}
```

### Respuesta esperada (JSON — error de validación)

```json
{
  "error": {
    "phone": ["The phone field is required."]
  }
}
```

### Registro del tool en el agente

El tool debe agregarse a la lista de tools disponibles del agente de ventas junto con la constante de configuración correspondiente en `SalesAgentConfig`:

```php
// En SalesAgentConfig — nueva constante de tool
const TOOL_CREATE_LEAD = 'create_lead';

// En el agente — instanciación del tool con los valores del contexto
new CreateLead(
    companyId: $this->companyId,
    source: $this->source,   // viene de ChatbotConversation->source
)
```

El `source` del chatbot debe estar disponible en el contexto del agente al momento de construir los tools. Esto implica pasar el `source` de la `ChatbotConversation` al agente junto con el `company_id`.

### Consideraciones técnicas

- El `company_id` y el `source` se inyectan en el constructor del tool; no forman parte de `parameters()`.
- La deduplicación se maneja en la Action: se busca lead activo por `(company_id, phone)` antes de crear.
- El scope `forCompany` del modelo garantiza aislamiento multi-tenant.
- La búsqueda de customer por teléfono es exacta (`=`), igual que en `get_customer_by_phone`.
- No aplicar policy/gate dentro del tool; la autorización ocurre antes de la invocación del agente.
- El agente debe invocar este tool al inicio de la conversación (se puede indicar en el prompt del sistema del agente) para garantizar que todo contacto quede registrado.
