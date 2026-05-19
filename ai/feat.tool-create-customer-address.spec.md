---
description: Tool de IA para crear una dirección asociada a un cliente existente
type: técnico
date: 2026-05-18
status: draft
user: Irwing Naranjo
---

# Tool: create_customer_address

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Permite que un agente de IA registre una nueva dirección para un cliente existente. El tool recibe el `customer_id` como parámetro y al menos uno de los campos de dirección, crea el registro en la base de datos y devuelve la dirección creada como JSON.

### Actores y roles

| Actor               | Acceso / comportamiento                                                     |
| ------------------- | --------------------------------------------------------------------------- |
| Agente IA           | Invoca el tool; recibe `company_id` del contexto del agente, no del usuario |
| Usuario autenticado | Interactúa con el agente; el agente actúa en su nombre                      |

### Flujo principal

1. El usuario indica: "Agrega esta dirección al cliente Juan Pérez: Av. Insurgentes Sur 1234, CDMX."
2. El agente (habiendo obtenido previamente el `customer_id` de Juan Pérez) invoca `create_customer_address` con el `customer_id` y los campos de dirección disponibles.
3. El tool verifica que el cliente pertenece a la empresa del agente.
4. Crea el registro en `sales_customer_addresses` con el `sort_order` calculado automáticamente.
5. Devuelve la dirección creada como JSON.
6. El agente informa al usuario que la dirección fue registrada.

### Reglas de negocio

- El cliente indicado en `customer_id` debe pertenecer a la empresa del agente; nunca se crean direcciones en clientes de otra empresa.
- Los clientes eliminados (`deleted_at IS NOT NULL`) no aceptan nuevas direcciones.
- Al menos uno de los campos `country_name`, `state_name` o `address` debe tener valor; no se permite crear una dirección completamente vacía.
- El `sort_order` se asigna automáticamente como el siguiente valor disponible (máximo actual + 1); el agente no lo controla.
- Un cliente puede tener múltiples direcciones.

### Datos que maneja el usuario

El `company_id` lo inyecta el agente desde su contexto. El agente obtiene el `customer_id` de herramientas previas (p. ej. `get_customer_by_phone`).

| Campo        | Origen | Obligatorio | Notas                                                   |
| ------------ | ------ | ----------- | ------------------------------------------------------- |
| company_id   | Agente | Sí          | UUID de la empresa activa en sesión                     |
| customer_id  | Agente | Sí          | UUID del cliente al que se asocia la dirección          |
| country_name | Agente | No          | Nombre del país; al menos uno de los tres debe tener valor |
| state_name   | Agente | No          | Nombre del estado/provincia                             |
| address      | Agente | No          | Dirección completa (calle, número, colonia, etc.)       |

---

## ESPECIFICACIÓN TÉCNICA

### Modelo de datos

```
tabla: sales_customer_addresses

Campos escritos por el tool:
- customer_id:  uuid        — FK a sales_customers (obligatorio)
- sort_order:   smallint    — calculado automáticamente (default: 0)
- country_name: string(120) — nullable
- state_name:   string(120) — nullable
- address:      text        — nullable, max 2000 caracteres

Campos devueltos en la respuesta:
- id:           uuid
- customer_id:  uuid
- sort_order:   integer
- country_name: string | null
- state_name:   string | null
- address:      string | null
```

### Validaciones

| Campo        | Reglas                                                                                    |
| ------------ | ----------------------------------------------------------------------------------------- |
| customer_id  | obligatorio, string, uuid, existente en `sales_customers` y perteneciente a `company_id` |
| country_name | opcional, string, max:120                                                                 |
| state_name   | opcional, string, max:120                                                                 |
| address      | opcional, string, max:2000                                                                |
| (regla)      | al menos uno de `country_name`, `state_name` o `address` debe tener valor                |

### Archivos involucrados

| Capa   | Ruta                                                      |
| ------ | --------------------------------------------------------- |
| Tool   | `app/Ai/Tools/CreateCustomerAddress.php`                  |
| Action | `app/Actions/AI/Tools/CreateCustomerAddressAction.php`    |
| Modelo | `app/Models/Sales/Customer.php`                           |
| Modelo | `app/Models/Sales/CustomerAddress.php`                    |

### Estructura del tool

```php
<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\CreateCustomerAddressAction;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Responses\ToolResponse;

class CreateCustomerAddress implements Tool
{
    public function __construct(private readonly string $companyId) {}

    public function name(): string
    {
        return 'create_customer_address';
    }

    public function description(): string
    {
        return 'Crea una nueva dirección para un cliente existente de la empresa. '
             . 'Requiere el customer_id y al menos uno de: country_name, state_name o address.';
    }

    public function parameters(): array
    {
        return [
            'customer_id' => [
                'type'        => 'string',
                'description' => 'UUID del cliente al que se asociará la nueva dirección.',
                'required'    => true,
            ],
            'country_name' => [
                'type'        => 'string',
                'description' => 'Nombre del país (máx. 120 caracteres). Opcional.',
                'required'    => false,
            ],
            'state_name' => [
                'type'        => 'string',
                'description' => 'Nombre del estado o provincia (máx. 120 caracteres). Opcional.',
                'required'    => false,
            ],
            'address' => [
                'type'        => 'string',
                'description' => 'Dirección completa: calle, número, colonia, etc. (máx. 2000 caracteres). Opcional.',
                'required'    => false,
            ],
        ];
    }

    public function handle(array $parameters): ToolResponse
    {
        $result = (new CreateCustomerAddressAction)->execute(
            $this->companyId,
            $parameters['customer_id'],
            $parameters['country_name'] ?? null,
            $parameters['state_name']   ?? null,
            $parameters['address']      ?? null,
        );

        if (isset($result['error'])) {
            return ToolResponse::error($result['error']);
        }

        return ToolResponse::make(json_encode($result));
    }
}
```

### Estructura de la Action

```php
<?php

namespace App\Actions\AI\Tools;

use App\Models\Sales\Customer;
use App\Models\Sales\CustomerAddress;

class CreateCustomerAddressAction
{
    public function execute(
        string  $companyId,
        string  $customerId,
        ?string $countryName,
        ?string $stateName,
        ?string $address,
    ): array {
        $customer = Customer::forCompany($companyId)
            ->find($customerId, ['id']);

        if ($customer === null) {
            return ['error' => 'No se encontró el cliente en la empresa.'];
        }

        if ($countryName === null && $stateName === null && $address === null) {
            return ['error' => 'Debes proporcionar al menos uno de: country_name, state_name o address.'];
        }

        $nextSortOrder = CustomerAddress::where('customer_id', $customerId)->max('sort_order') + 1;

        $customerAddress = CustomerAddress::create([
            'customer_id'  => $customerId,
            'sort_order'   => $nextSortOrder,
            'country_name' => $countryName !== '' ? $countryName : null,
            'state_name'   => $stateName   !== '' ? $stateName   : null,
            'address'      => $address     !== '' ? $address      : null,
        ]);

        return $customerAddress->only([
            'id', 'customer_id', 'sort_order',
            'country_name', 'state_name', 'address',
        ]);
    }
}
```

### Respuesta esperada (JSON)

```json
{
  "id": "uuid",
  "customer_id": "uuid",
  "sort_order": 2,
  "country_name": "México",
  "state_name": "Ciudad de México",
  "address": "Av. Insurgentes Sur 1234, Col. Del Valle"
}
```

### Consideraciones técnicas

- El `company_id` se inyecta en el constructor del tool por el agente; no forma parte de `parameters()`.
- El scope `forCompany` del modelo garantiza aislamiento multi-tenant: el `customer_id` se valida contra la empresa activa.
- El `sort_order` se calcula como `MAX(sort_order) + 1` sobre las direcciones existentes del cliente; si no tiene ninguna, queda en `1`.
- Las cadenas vacías se normalizan a `null` antes de persistir, igual que en `SyncCustomerAddressesAction`.
- El tool devuelve `ToolResponse::error` con un mensaje descriptivo si el cliente no existe o si los tres campos de dirección están ausentes.
- No aplicar policy/gate dentro del tool; la autorización ocurre antes de la invocación del agente.
