---
description: Tool de IA para crear un cliente en la empresa en contexto
type: técnico
date: 2026-05-18
status: draft
user: Irwing Naranjo
---

# Tool: create_customer

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Permite que un agente de IA cree un nuevo cliente dentro de la empresa en contexto. El tool recibe los datos del cliente desde los parámetros que el agente extrae de la conversación, y el `company_id` lo toma directamente del contexto de sesión del agente.

### Actores y roles

| Actor               | Acceso / comportamiento                                                     |
| ------------------- | --------------------------------------------------------------------------- |
| Agente IA           | Invoca el tool; recibe `company_id` del contexto del agente, no del usuario |
| Usuario autenticado | Proporciona los datos del cliente mediante la conversación con el agente    |

### Flujo principal

1. El usuario le pide al agente: "Registra a Juan Pérez con el teléfono 555-1234."
2. El agente extrae `full_name` y `phone` de la conversación e invoca `create_customer`.
3. El tool valida que el teléfono no esté registrado ya en esa empresa.
4. Crea el cliente (y sus direcciones opcionales) dentro de la empresa en contexto.
5. Devuelve el cliente creado como JSON.
6. El agente confirma al usuario que el cliente fue registrado.

### Reglas de negocio

- El cliente queda asociado a la empresa del `company_id` inyectado por el agente.
- El teléfono debe ser único por empresa (no puede existir otro cliente activo con el mismo número).
- Los clientes eliminados (`deleted_at IS NOT NULL`) no bloquean la reutilización del teléfono.
- Las direcciones son opcionales; si se proporcionan, se asocian al cliente en el mismo acto de creación.
- Cada dirección puede tener país, estado y dirección textual, todos opcionales individualmente.
- Se permiten hasta 20 direcciones por cliente.

### Datos que maneja el tool

| Campo                       | Origen    | Obligatorio | Notas                                            |
| --------------------------- | --------- | ----------- | ------------------------------------------------ |
| company_id                  | Agente    | Sí          | UUID de la empresa activa en sesión              |
| full_name                   | Parámetro | Sí          | Nombre completo del cliente, máx. 255 caracteres |
| phone                       | Parámetro | Sí          | Teléfono, máx. 40 caracteres, único por empresa  |
| addresses                   | Parámetro | No          | Arreglo de direcciones, máx. 20 elementos        |
| addresses[].country_name    | Parámetro | No          | País, máx. 120 caracteres                        |
| addresses[].state_name      | Parámetro | No          | Estado / provincia, máx. 120 caracteres          |
| addresses[].address         | Parámetro | No          | Dirección textual, máx. 2000 caracteres          |

---

## ESPECIFICACIÓN TÉCNICA

### Modelo de datos

```
tabla: sales_customers

Campos escritos por el tool:
- id:           uuid (generado automáticamente)
- company_id:   uuid (inyectado por el agente)
- full_name:    string
- phone:        string(40)

tabla: sales_customer_addresses

Campos escritos por el tool (por cada dirección):
- id:             uuid (generado automáticamente)
- customer_id:    uuid (FK al cliente recién creado)
- sort_order:     unsigned smallint (posición en el arreglo, base 0)
- country_name:   string(120) | null
- state_name:     string(120) | null
- address:        text | null
```

### Validaciones

| Campo                    | Reglas                                                                          |
| ------------------------ | ------------------------------------------------------------------------------- |
| company_id               | Obligatorio, uuid existente en `configuration_companies`                        |
| full_name                | Obligatorio, string, máx. 255                                                   |
| phone                    | Obligatorio, string, máx. 40, único en `sales_customers` para el `company_id` dado (ignorando soft-deletes) |
| addresses                | Opcional, array, máx. 20 elementos                                              |
| addresses[].country_name | Opcional, string, máx. 120                                                      |
| addresses[].state_name   | Opcional, string, máx. 120                                                      |
| addresses[].address      | Opcional, string, máx. 2000                                                     |

### Archivos involucrados

| Capa   | Ruta                                               |
| ------ | -------------------------------------------------- |
| Tool   | `app/Ai/Tools/CreateCustomer.php`                  |
| Action | `app/Actions/AI/Tools/CreateCustomerAction.php`    |
| Action | `app/Actions/Sales/Customers/CreateCustomerAction.php` (reutilizada) |
| Modelo | `app/Models/Sales/Customer.php`                    |

### Estructura del tool

```php
<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\CreateCustomerAction;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Responses\ToolResponse;

class CreateCustomer implements Tool
{
    public function __construct(private readonly string $companyId) {}

    public function name(): string
    {
        return 'create_customer';
    }

    public function description(): string
    {
        return 'Crea un nuevo cliente en la empresa activa. '
             . 'Requiere nombre completo y teléfono; opcionalmente acepta un arreglo de direcciones.';
    }

    public function parameters(): array
    {
        return [
            'full_name' => [
                'type'        => 'string',
                'description' => 'Nombre completo del cliente.',
                'required'    => true,
            ],
            'phone' => [
                'type'        => 'string',
                'description' => 'Teléfono del cliente. Debe ser único dentro de la empresa.',
                'required'    => true,
            ],
            'addresses' => [
                'type'        => 'array',
                'description' => 'Lista de direcciones del cliente (máx. 20). Cada elemento puede tener country_name, state_name y address.',
                'required'    => false,
                'items'       => [
                    'type'       => 'object',
                    'properties' => [
                        'country_name' => ['type' => 'string', 'description' => 'País.'],
                        'state_name'   => ['type' => 'string', 'description' => 'Estado o provincia.'],
                        'address'      => ['type' => 'string', 'description' => 'Dirección textual completa.'],
                    ],
                ],
            ],
        ];
    }

    public function handle(array $parameters): ToolResponse
    {
        $result = (new CreateCustomerAction)->execute($this->companyId, $parameters);

        if (isset($result['error'])) {
            return ToolResponse::make(json_encode($result));
        }

        return ToolResponse::make(json_encode($result));
    }
}
```

### Estructura de la Action

```php
<?php

namespace App\Actions\AI\Tools;

use App\Actions\Sales\Customers\CreateCustomerAction as SalesCreateCustomerAction;
use App\Models\Sales\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateCustomerAction
{
    public function execute(string $companyId, array $parameters): array
    {
        $validator = Validator::make($parameters, [
            'full_name'              => ['required', 'string', 'max:255'],
            'phone'                  => [
                'required',
                'string',
                'max:40',
                Rule::unique('sales_customers', 'phone')
                    ->where('company_id', $companyId)
                    ->whereNull('deleted_at'),
            ],
            'addresses'              => ['nullable', 'array', 'max:20'],
            'addresses.*.country_name' => ['nullable', 'string', 'max:120'],
            'addresses.*.state_name'   => ['nullable', 'string', 'max:120'],
            'addresses.*.address'      => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validator->fails()) {
            return ['error' => $validator->errors()->toArray()];
        }

        $customer = (new SalesCreateCustomerAction)->execute($companyId, $validator->validated());

        return $customer->load('addresses')->toArray();
    }
}
```

### Respuesta esperada (JSON — éxito)

```json
{
  "id": "uuid",
  "company_id": "uuid",
  "full_name": "Juan Pérez",
  "phone": "555-1234",
  "created_at": "2026-05-18T12:00:00.000000Z",
  "updated_at": "2026-05-18T12:00:00.000000Z",
  "deleted_at": null,
  "addresses": [
    {
      "id": "uuid",
      "customer_id": "uuid",
      "sort_order": 0,
      "country_name": "México",
      "state_name": "CDMX",
      "address": "Av. Insurgentes Sur 1234, Col. Del Valle"
    }
  ]
}
```

### Respuesta esperada (JSON — error de validación)

```json
{
  "error": {
    "phone": ["The phone has already been taken."]
  }
}
```

### Consideraciones técnicas

- El `company_id` se inyecta en el constructor del tool por el agente; no forma parte de `parameters()`.
- La validación ocurre dentro de la Action de IA antes de delegar a la Action de dominio.
- Se reutiliza `app/Actions/Sales/Customers/CreateCustomerAction.php` para la creación real, garantizando que la lógica de negocio (transacción DB + sincronización de direcciones) sea compartida con el flujo web.
- El scope `forCompany` del modelo garantiza aislamiento multi-tenant.
- No aplicar policy/gate dentro del tool; la autorización ocurre antes de la invocación del agente.
- Los errores de validación se devuelven como JSON con clave `error` para que el agente los interprete y comunique al usuario de forma natural.
