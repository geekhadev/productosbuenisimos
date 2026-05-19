---
description: Tool de IA para obtener la información básica de un cliente por número de teléfono
type: técnico
date: 2026-05-18
status: draft
user: Irwing Naranjo
---

# Tool: get_customer_by_phone

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Permite que un agente de IA consulte la información básica de un cliente registrado a partir de su número de teléfono. El tool devuelve el nombre, teléfono y direcciones del cliente como JSON para que el agente pueda responder preguntas como "¿Tenemos registrado a alguien con el número 555-1234?" o "¿Cuál es la dirección del cliente con ese teléfono?".

### Actores y roles

| Actor               | Acceso / comportamiento                                                     |
| ------------------- | --------------------------------------------------------------------------- |
| Agente IA           | Invoca el tool; recibe `company_id` del contexto del agente, no del usuario |
| Usuario autenticado | Interactúa con el agente; el agente actúa en su nombre                      |

### Flujo principal

1. El usuario pregunta: "¿Tenemos registrado al cliente con el teléfono 555-1234?"
2. El agente invoca `get_customer_by_phone` con `phone = "555-1234"`.
3. El tool busca el cliente por número de teléfono dentro de la empresa.
4. Devuelve la información básica del cliente como JSON, o un error si no se encuentra.
5. El agente interpreta el JSON y responde al usuario.

### Reglas de negocio

- La búsqueda es exacta sobre el campo `phone`.
- Solo se devuelven clientes de la empresa del agente; nunca datos de otras empresas.
- Los clientes con `deleted_at IS NOT NULL` no son encontrables.
- Si el teléfono no coincide con ningún cliente registrado, el tool devuelve un error descriptivo.
- Se incluyen las direcciones registradas del cliente para que el agente pueda informarlas si el usuario lo solicita.
- No se expone información sensible más allá del nombre, teléfono y direcciones.

### Datos que maneja el usuario

El `company_id` lo inyecta el agente desde su contexto. El usuario solo provee el número de teléfono de forma conversacional.

| Campo      | Origen | Obligatorio | Notas                               |
| ---------- | ------ | ----------- | ----------------------------------- |
| company_id | Agente | Sí          | UUID de la empresa activa en sesión |
| phone      | Agente | Sí          | Número de teléfono del cliente      |

---

## ESPECIFICACIÓN TÉCNICA

### Modelo de datos

```
tabla: sales_customers

Campos devueltos por el tool:
- id:        uuid
- full_name: string
- phone:     string

tabla: sales_customer_addresses (relación hasMany)

Campos devueltos por el tool:
- id:           uuid
- sort_order:   integer
- country_name: string
- state_name:   string
- address:      string
```

### Validaciones

| Campo | Reglas                       |
| ----- | ---------------------------- |
| phone | obligatorio, string, max:255 |

### Archivos involucrados

| Capa   | Ruta                                                  |
| ------ | ----------------------------------------------------- |
| Tool   | `app/Ai/Tools/GetCustomerByPhone.php`                 |
| Action | `app/Actions/AI/Tools/GetCustomerByPhoneAction.php`   |
| Modelo | `app/Models/Sales/Customer.php`                       |
| Modelo | `app/Models/Sales/CustomerAddress.php`                |

### Estructura del tool

```php
<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\GetCustomerByPhoneAction;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Responses\ToolResponse;

class GetCustomerByPhone implements Tool
{
    public function __construct(private readonly string $companyId) {}

    public function name(): string
    {
        return 'get_customer_by_phone';
    }

    public function description(): string
    {
        return 'Busca un cliente registrado por su número de teléfono dentro de la empresa '
             . 'y devuelve su información básica (nombre, teléfono y direcciones) como JSON.';
    }

    public function parameters(): array
    {
        return [
            'phone' => [
                'type'        => 'string',
                'description' => 'Número de teléfono del cliente a consultar.',
                'required'    => true,
            ],
        ];
    }

    public function handle(array $parameters): ToolResponse
    {
        $customer = (new GetCustomerByPhoneAction)->execute(
            $this->companyId,
            $parameters['phone']
        );

        if ($customer === null) {
            return ToolResponse::error('No se encontró ningún cliente con ese número de teléfono.');
        }

        return ToolResponse::make(json_encode($customer));
    }
}
```

### Estructura de la Action

```php
<?php

namespace App\Actions\AI\Tools;

use App\Models\Sales\Customer;

class GetCustomerByPhoneAction
{
    public function execute(string $companyId, string $phone): ?array
    {
        $customer = Customer::forCompany($companyId)
            ->where('phone', $phone)
            ->with(['addresses' => fn ($q) => $q->select([
                'id', 'customer_id', 'sort_order',
                'country_name', 'state_name', 'address',
            ])])
            ->first(['id', 'full_name', 'phone']);

        if ($customer === null) {
            return null;
        }

        return [
            'id'        => $customer->id,
            'full_name' => $customer->full_name,
            'phone'     => $customer->phone,
            'addresses' => $customer->addresses->toArray(),
        ];
    }
}
```

### Respuesta esperada (JSON)

```json
{
  "id": "uuid",
  "full_name": "Juan Pérez",
  "phone": "555-1234",
  "addresses": [
    {
      "id": "uuid",
      "customer_id": "uuid",
      "sort_order": 1,
      "country_name": "México",
      "state_name": "Ciudad de México",
      "address": "Av. Insurgentes Sur 1234, Col. Del Valle"
    }
  ]
}
```

### Consideraciones técnicas

- El `company_id` se inyecta en el constructor del tool por el agente; no forma parte de `parameters()`.
- El scope `forCompany` del modelo garantiza aislamiento multi-tenant; el cliente nunca puede pertenecer a otra empresa.
- La búsqueda de teléfono es exacta (`=`), no parcial, para evitar ambigüedades.
- Solo se seleccionan los campos estrictamente necesarios (`id`, `full_name`, `phone`) para no exponer datos innecesarios.
- Las direcciones se cargan con `with` en una sola consulta adicional; se seleccionan solo los campos informativos.
- El `company_id` no se incluye en la respuesta JSON; el agente ya lo conoce por su contexto.
- No aplicar policy/gate dentro del tool; la autorización ocurre antes de la invocación del agente.
