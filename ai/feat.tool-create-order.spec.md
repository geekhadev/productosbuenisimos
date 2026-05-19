---
description: Tool de IA para crear un pedido de venta vinculado a empresa, cliente, dirección y productos
type: técnico
date: 2026-05-18
status: draft
user: Irwing Naranjo
---

# Tool: create_order

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Permite que un agente de IA cree un nuevo pedido de venta para la empresa activa en sesión. El agente recibe del usuario el cliente, la dirección de entrega y los productos (con cantidades), y los pasa al tool para registrar el pedido en el sistema. El tool devuelve el pedido creado como JSON para que el agente confirme la operación al usuario.

### Actores y roles

| Actor               | Acceso / comportamiento                                                         |
| ------------------- | ------------------------------------------------------------------------------- |
| Agente IA           | Invoca el tool; recibe `company_id` del contexto del agente, no del usuario     |
| Usuario autenticado | Proporciona cliente, dirección, productos y cantidades al agente                |

### Flujo principal

1. El usuario le indica al agente los datos del pedido: cliente, dirección y productos.
2. El agente invoca `create_order` pasando `company_id` desde su contexto y el resto como parámetros.
3. El tool valida que el cliente y la dirección pertenezcan a la empresa.
4. El tool valida que cada producto exista, sea activo y pertenezca a la empresa.
5. El tool calcula `line_total` por ítem y `total_amount` del pedido.
6. Registra el pedido y sus ítems en la base de datos.
7. Devuelve el pedido creado como JSON.
8. El agente interpreta el JSON y confirma la creación al usuario.

### Reglas de negocio

- El pedido debe pertenecer a la empresa indicada en `company_id`; nunca se cruzan datos entre empresas.
- El cliente (`customer_id`) debe existir en la empresa y no estar eliminado (soft delete).
- La dirección (`address_id`) debe pertenecer al cliente indicado.
- El pedido debe tener al menos un ítem.
- Cada producto puede aparecer una sola vez por pedido; la cantidad debe ser ≥ 1.
- Solo se pueden agregar productos activos (`is_active = true`) y no eliminados.
- Si no se proporciona `unit_price` para un ítem, se usa el precio actual del producto (`price`).
- `line_total` se calcula como `quantity × unit_price` (precisión de 2 decimales con `bcmul`).
- `total_amount` es la suma de todos los `line_total`.
- El nombre del pedido (`name`) debe ser único por empresa. Si el agente no lo recibe del usuario, genera uno automáticamente con el formato `Pedido-{YYYYMMDD-HHmmss}`.
- No se aplica policy/gate dentro del tool; la autorización ocurre antes de la invocación del agente.

### Datos que maneja el tool

| Campo            | Origen   | Obligatorio | Notas                                                              |
| ---------------- | -------- | ----------- | ------------------------------------------------------------------ |
| company_id       | Agente   | Sí          | UUID inyectado desde el contexto del agente                        |
| customer_id      | Parámetro| Sí          | UUID del cliente; debe existir en la empresa y no estar eliminado  |
| address_id       | Parámetro| Sí          | UUID de la dirección; debe pertenecer al customer_id indicado      |
| items            | Parámetro| Sí          | Array con al menos un ítem                                         |
| items[].product_id | Parámetro| Sí        | UUID del producto; activo y perteneciente a la empresa             |
| items[].quantity | Parámetro| Sí          | Entero ≥ 1                                                         |
| items[].unit_price | Parámetro| No        | Decimal ≥ 0; si se omite, se usa el precio actual del producto     |
| name             | Parámetro| No          | Nombre del pedido; si se omite se genera automáticamente           |

---

## ESPECIFICACIÓN TÉCNICA

### Modelo de datos

```
tabla: sales_orders

Campos escritos por el tool:
- id:            uuid (generado automáticamente)
- company_id:    uuid (FK → configuration_companies)
- name:          string — único por empresa
- customer_id:   uuid (FK → sales_customers)
- address_id:    uuid (FK → sales_customer_addresses)
- total_amount:  decimal(12,2) — suma de todos los line_total

tabla: sales_order_items

Campos escritos por el tool:
- id:            uuid (generado automáticamente)
- order_id:      uuid (FK → sales_orders)
- product_id:    uuid (FK → stock_products) — único por pedido
- quantity:      unsigned integer ≥ 1
- unit_price:    decimal(12,2)
- line_total:    decimal(12,2) — quantity × unit_price
```

### Validaciones

| Campo              | Reglas                                                                              |
| ------------------ | ----------------------------------------------------------------------------------- |
| company_id         | obligatorio, uuid, existente en configuration_companies                             |
| customer_id        | obligatorio, uuid, existente en sales_customers (misma empresa, sin soft delete)    |
| address_id         | obligatorio, uuid, existente en sales_customer_addresses (pertenece al customer_id) |
| items              | obligatorio, array, mínimo 1 elemento                                               |
| items[].product_id | obligatorio, uuid, existente en stock_products (misma empresa, activo, sin delete)  |
| items[].quantity   | obligatorio, entero, mínimo 1                                                       |
| items[].unit_price | opcional, decimal, mínimo 0; default: precio actual del producto                    |
| name               | opcional, string, máximo 255; si se omite se genera automáticamente; único/empresa  |

### Archivos involucrados

| Capa   | Ruta                                                                        |
| ------ | --------------------------------------------------------------------------- |
| Tool   | `app/Ai/Tools/CreateOrder.php`                                              |
| Action | `app/Actions/AI/Tools/CreateOrderAction.php`                                |
| Modelo | `app/Models/Sales/Order.php`                                                |
| Modelo | `app/Models/Sales/OrderItem.php`                                            |
| Modelo | `app/Models/Sales/Customer.php`                                             |
| Modelo | `app/Models/Sales/CustomerAddress.php`                                      |
| Modelo | `app/Models/Stock/Product.php`                                              |
| Action | `app/Actions/Sales/Orders/StoreOrderAction.php` (reutilizada internamente)  |

### Estructura del tool

```php
<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\CreateOrderAction;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Responses\ToolResponse;

class CreateOrder implements Tool
{
    public function __construct(private readonly string $companyId) {}

    public function name(): string
    {
        return 'create_order';
    }

    public function description(): string
    {
        return 'Crea un nuevo pedido de venta para la empresa en sesión. '
             . 'Recibe el cliente, la dirección de entrega y la lista de productos con cantidades. '
             . 'Devuelve el pedido creado como JSON.';
    }

    public function parameters(): array
    {
        return [
            'customer_id' => [
                'type'        => 'string',
                'description' => 'UUID del cliente al que pertenece el pedido.',
                'required'    => true,
            ],
            'address_id' => [
                'type'        => 'string',
                'description' => 'UUID de la dirección de entrega del cliente.',
                'required'    => true,
            ],
            'items' => [
                'type'        => 'array',
                'description' => 'Lista de productos del pedido. Debe contener al menos un elemento.',
                'required'    => true,
                'items'       => [
                    'type'       => 'object',
                    'properties' => [
                        'product_id' => [
                            'type'        => 'string',
                            'description' => 'UUID del producto.',
                        ],
                        'quantity' => [
                            'type'        => 'integer',
                            'description' => 'Cantidad del producto. Mínimo 1.',
                            'minimum'     => 1,
                        ],
                        'unit_price' => [
                            'type'        => 'number',
                            'description' => 'Precio unitario. Si se omite se usa el precio vigente del producto.',
                        ],
                    ],
                    'required' => ['product_id', 'quantity'],
                ],
            ],
            'name' => [
                'type'        => 'string',
                'description' => 'Nombre del pedido. Si se omite se genera automáticamente como "Pedido-{YYYYMMDD-HHmmss}".',
                'required'    => false,
            ],
        ];
    }

    public function handle(array $parameters): ToolResponse
    {
        $order = (new CreateOrderAction)->execute($this->companyId, $parameters);

        return ToolResponse::make(json_encode($order));
    }
}
```

### Estructura de la Action

```php
<?php

namespace App\Actions\AI\Tools;

use App\Actions\Sales\Orders\StoreOrderAction;
use App\Models\Sales\Customer;
use App\Models\Sales\CustomerAddress;
use App\Models\Stock\Product;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CreateOrderAction
{
    public function execute(string $companyId, array $parameters): array
    {
        $name       = $parameters['name'] ?? 'Pedido-' . Carbon::now()->format('Ymd-His');
        $customerId = $parameters['customer_id'];
        $addressId  = $parameters['address_id'];
        $items      = $parameters['items'];

        $this->validateCustomer($companyId, $customerId);
        $this->validateAddress($customerId, $addressId);
        $this->validateItems($companyId, $items);

        $order = (new StoreOrderAction)->execute($companyId, [
            'name'        => $name,
            'customer_id' => $customerId,
            'address_id'  => $addressId,
            'items'       => $items,
        ]);

        return $order->load(['customer', 'address', 'items.product'])->toArray();
    }

    private function validateCustomer(string $companyId, string $customerId): void
    {
        $exists = Customer::forCompany($companyId)
            ->whereKey($customerId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'customer_id' => 'El cliente no existe o no pertenece a la empresa.',
            ]);
        }
    }

    private function validateAddress(string $customerId, string $addressId): void
    {
        $exists = CustomerAddress::where('customer_id', $customerId)
            ->whereKey($addressId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'address_id' => 'La dirección no existe o no pertenece al cliente indicado.',
            ]);
        }
    }

    private function validateItems(string $companyId, array $items): void
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'El pedido debe contener al menos un producto.',
            ]);
        }

        $productIds = array_column($items, 'product_id');

        $foundCount = Product::forCompany($companyId)
            ->where('is_active', true)
            ->whereIn('id', $productIds)
            ->count();

        if ($foundCount !== count($productIds)) {
            throw ValidationException::withMessages([
                'items' => 'Uno o más productos no existen, no son activos o no pertenecen a la empresa.',
            ]);
        }
    }
}
```

### Respuesta esperada (JSON)

```json
{
  "id": "uuid",
  "company_id": "uuid",
  "name": "Pedido-20260518-143000",
  "customer_id": "uuid",
  "address_id": "uuid",
  "total_amount": "450.00",
  "customer": {
    "id": "uuid",
    "full_name": "Juan Pérez",
    "phone": "1234567890"
  },
  "address": {
    "id": "uuid",
    "country_name": "México",
    "state_name": "CDMX",
    "address": "Av. Insurgentes Sur 1234"
  },
  "items": [
    {
      "id": "uuid",
      "product_id": "uuid",
      "quantity": 3,
      "unit_price": "150.00",
      "line_total": "450.00",
      "product": {
        "id": "uuid",
        "name": "Imanes de Neodimio Autoadhesivos",
        "code": "PROD001",
        "sku": "SKU-001"
      }
    }
  ]
}
```

### Consideraciones técnicas

- El `company_id` se inyecta en el constructor del tool por el agente; no forma parte de `parameters()`.
- El scope `forCompany` del modelo garantiza aislamiento multi-tenant en cada consulta.
- Si `unit_price` se omite en un ítem, `StoreOrderAction` usa el `price` vigente del producto vía `SyncOrderItemsAction::normalizeLines`.
- `line_total` se calcula con `bcmul($quantity, $unit_price, 2)` para evitar errores de punto flotante.
- `total_amount` es la suma de todos los `line_total`, calculada dentro de `StoreOrderAction`.
- El nombre único por empresa se garantiza a nivel de base de datos (índice único compuesto `[company_id, name]`); si hay colisión por el nombre auto-generado, el agente debe reintentar con otro valor.
- Si la validación falla, el tool devuelve un mensaje de error descriptivo en lugar del pedido, para que el agente informe al usuario.
- No aplicar policy/gate dentro del tool; la autorización ocurre antes de la invocación del agente.
