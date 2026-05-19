---
description: Tool de IA para listar todos los productos activos de una empresa
type: técnico
date: 2026-05-13
status: draft
user: Irwing Naranjo
---

# Tool: get_products

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Permite que un agente de IA obtenga el catálogo completo de productos activos de una empresa. El tool devuelve todos los productos en un JSON estructurado para que el agente lo interprete y responda preguntas del usuario como disponibilidad, precios o existencia de productos.

### Actores y roles

| Actor               | Acceso / comportamiento                                                    |
| ------------------- | -------------------------------------------------------------------------- |
| Agente IA           | Invoca el tool; recibe `company_id` del contexto del agente, no del usuario |
| Usuario autenticado | Interactúa con el agente; el agente actúa en su nombre                     |

### Flujo principal

1. El usuario le pregunta al agente: "¿Qué productos tenemos disponibles?"
2. El agente invoca `get_products` pasando el `company_id` de su contexto.
3. El tool consulta todos los productos activos de esa empresa.
4. Devuelve el listado completo como JSON.
5. El agente interpreta el JSON y responde al usuario.

### Reglas de negocio

- Solo se devuelven productos activos (`is_active = true`).
- Solo se devuelven productos de la empresa indicada en `company_id`; nunca datos de otras empresas.
- Los productos eliminados (`deleted_at IS NOT NULL`) nunca se incluyen.
- No hay paginado, búsqueda ni ordenamiento; el agente recibe todo y razona sobre los datos.

### Datos que maneja el usuario

El tool no expone parámetros al usuario. El `company_id` lo inyecta el agente desde su contexto de sesión.

| Campo      | Origen  | Notas                                 |
| ---------- | ------- | ------------------------------------- |
| company_id | Agente  | UUID de la empresa activa en sesión   |

---

## ESPECIFICACIÓN TÉCNICA

### Modelo de datos

```
tabla: stock_products

Campos devueltos por el tool:
- id:              uuid
- name:            string
- code:            string
- sku:             string
- price:           decimal(12,2)
- description:     text | null
- weight:          decimal(12,3)
- width:           decimal(12,3)
- length:          decimal(12,3)
- height:          decimal(12,3)
- volume:          decimal(12,3)
```

### Validaciones

| Campo      | Reglas                                                         |
| ---------- | -------------------------------------------------------------- |
| company_id | obligatorio, string, uuid existente en configuration_companies |

### Archivos involucrados

| Capa   | Ruta                                          |
| ------ | --------------------------------------------- |
| Tool   | `app/Ai/Tools/GetProducts.php`                |
| Action | `app/Actions/AI/Tools/GetProductsAction.php`  |
| Modelo | `app/Models/Stock/Product.php`                |

### Estructura del tool

```php
<?php

namespace App\Ai\Tools;

use App\Actions\AI\Tools\GetProductsAction;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Responses\ToolResponse;

class GetProducts implements Tool
{
    public function __construct(private readonly string $companyId) {}

    public function name(): string
    {
        return 'get_products';
    }

    public function description(): string
    {
        return 'Devuelve todos los productos activos del catálogo de la empresa '
             . 'como JSON para que el agente pueda responder preguntas sobre disponibilidad y precios.';
    }

    public function parameters(): array
    {
        return [];
    }

    public function handle(array $parameters): ToolResponse
    {
        $products = (new GetProductsAction)->execute($this->companyId);

        return ToolResponse::make(json_encode($products));
    }
}
```

### Estructura de la Action

```php
<?php

namespace App\Actions\AI\Tools;

use App\Models\Stock\Product;

class GetProductsAction
{
    public function execute(string $companyId): array
    {
        return Product::forCompany($companyId)
            ->where('is_active', true)
            ->get([
                'id', 'name', 'code', 'sku', 'price',
                'description',
                'weight', 'width', 'length', 'height', 'volume',
            ])
            ->toArray();
    }
}
```

### Respuesta esperada (JSON)

```json
[
  {
    "id": "uuid",
    "name": "Imanes de Neodimio Autoadhesivos",
    "code": "PROD001",
    "sku": "SKU-001",
    "price": "150.00",
    "description": "Imanes autoadhesivos de alta potencia",
    "weight": "0.050",
    "width": "2.000",
    "length": "2.000",
    "height": "0.500",
    "volume": "2.000"
  }
]
```

### Consideraciones técnicas

- El `company_id` se inyecta en el constructor del tool por el agente; no forma parte de `parameters()`.
- El scope `forCompany` del modelo garantiza aislamiento multi-tenant.
- Sin paginación ni filtros: el agente recibe el dataset completo y razona sobre él.
- Los decimales se serializan como string para preservar precisión en el JSON.
- No aplicar policy/gate dentro del tool; la autorización ocurre antes de la invocación del agente.
