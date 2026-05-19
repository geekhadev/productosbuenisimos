---
description: Agente de IA para cerrar ventas creando cliente, dirección y pedido de forma conversacional
type: técnico
date: 2026-05-18
status: draft
user: Irwing Naranjo
---

# Agente: SalesAgent

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Agente conversacional de ventas que guía al usuario a través del proceso completo de cierre de una venta. El agente recoge los datos del cliente (creándolo si no existe), confirma o crea la dirección de entrega, muestra el catálogo de productos, y registra el pedido. Al finalizar presenta un resumen claro del pedido creado.

### Actores y roles

| Actor               | Acceso / comportamiento                                                                        |
| ------------------- | ---------------------------------------------------------------------------------------------- |
| Agente IA           | Conduce la conversación; invoca tools con `company_id` de su contexto, nunca del usuario       |
| Usuario autenticado | Operador de ventas que interactúa con el agente para registrar pedidos de clientes             |

### Flujo principal

1. El usuario inicia la conversación indicando que quiere registrar un pedido.
2. El agente solicita el teléfono del cliente e invoca `get_customer_by_phone`.
3. **Si el cliente existe**: confirma nombre y dirección(es) disponibles.
4. **Si el cliente no existe**: solicita nombre completo e invoca `create_customer`.
5. Si el cliente no tiene dirección o el usuario desea una nueva, solicita los datos de entrega e invoca `create_customer_address`.
6. El agente invoca `get_products` y presenta el catálogo al usuario.
7. El usuario selecciona productos y cantidades.
8. El agente confirma los detalles del pedido (cliente, dirección, ítems, total estimado).
9. El usuario aprueba; el agente invoca `create_order`.
10. El agente presenta el resumen final del pedido creado.

### Reglas de negocio

- El agente **nunca** expone `company_id` al usuario; lo inyecta desde su contexto.
- El agente siempre busca primero al cliente por teléfono antes de intentar crearlo.
- Si el cliente ya existe, el agente no puede modificar sus datos principales (`full_name`, `phone`).
- El agente siempre confirma los datos del pedido con el usuario antes de invocar `create_order`.
- Si `create_order` falla por colisión de nombre, el agente reintenta con un nombre distinto generado automáticamente, sin interrumpir al usuario.
- Al finalizar, el agente presenta siempre el resumen completo del pedido (número, cliente, dirección, ítems y total).

---

## ESPECIFICACIÓN TÉCNICA

### Clase del agente

```
Namespace:  App\Ai\Agents
Clase:      SalesAgent
Artisan:    php artisan make:agent SalesAgent
```

### Interfaces implementadas

| Interfaz         | Propósito                                              |
| ---------------- | ------------------------------------------------------ |
| `Agent`          | Requerida; expone `instructions()`                     |
| `Conversational` | Historial de conversación persistido automáticamente   |
| `HasTools`       | Expone `tools()` con todas las herramientas de ventas  |

### Traits utilizados

| Trait                   | Propósito                                                                                          |
| ----------------------- | -------------------------------------------------------------------------------------------------- |
| `Promptable`            | Habilita el método `prompt()` y la resolución de dependencias vía `make()`                        |
| `RemembersConversations`| Persiste y recupera el historial automáticamente en las tablas `agent_conversations` y `agent_conversation_messages` |

> **Requisito de migración**: publicar y correr las migraciones del AI SDK para crear las tablas de conversación.
> ```bash
> php artisan vendor:publish --tag=ai-migrations
> php artisan migrate
> ```

### Tools registradas

El agente registra **todas** las tools de ventas existentes. Cada tool recibe `company_id` vía constructor, nunca como parámetro del usuario.

| Tool                   | Clase                               | Cuándo la usa el agente                                     |
| ---------------------- | ----------------------------------- | ----------------------------------------------------------- |
| `get_products`         | `App\Ai\Tools\GetProducts`          | Para mostrar el catálogo al usuario                         |
| `get_customer_by_phone`| `App\Ai\Tools\GetCustomerByPhone`   | Al inicio, para buscar si el cliente ya existe              |
| `create_customer`      | `App\Ai\Tools\CreateCustomer`       | Si el cliente no existe, para crearlo con nombre y teléfono |
| `create_customer_address` | `App\Ai\Tools\CreateCustomerAddress` | Para agregar una dirección de entrega al cliente          |
| `create_order`         | `App\Ai\Tools\CreateOrder`          | Para registrar el pedido final                              |

### Instrucciones del agente

Las instrucciones del agente **no se definen inline** en la clase PHP. Se cargan desde un archivo Markdown independiente:

```
resources/ai/prompts/agent-ventas.md
```

El método `instructions()` lee ese archivo en tiempo de ejecución:

```php
public function instructions(): string
{
    return file_get_contents(resource_path('ai/prompts/agent-ventas.md'));
}
```

### Contexto de conversación

El agente persiste la conversación por usuario usando `RemembersConversations`.

- **Nueva conversación**:
  ```php
  $response = SalesAgent::make(companyId: $companyId)
      ->forUser($user)
      ->prompt('Quiero registrar un pedido');

  $conversationId = $response->conversationId;
  ```

- **Continuación**:
  ```php
  $response = SalesAgent::make(companyId: $companyId)
      ->continue($conversationId, as: $user)
      ->prompt($userMessage);
  ```

### Estructura de la clase

```php
<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CreateCustomer;
use App\Ai\Tools\CreateCustomerAddress;
use App\Ai\Tools\CreateOrder;
use App\Ai\Tools\GetCustomerByPhone;
use App\Ai\Tools\GetProducts;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

class SalesAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function __construct(private readonly string $companyId) {}

    public function instructions(): string
    {
        return file_get_contents(resource_path('ai/prompts/agent-ventas.md'));
    }

    public function tools(): iterable
    {
        return [
            new GetProducts($this->companyId),
            new GetCustomerByPhone($this->companyId),
            new CreateCustomer($this->companyId),
            new CreateCustomerAddress($this->companyId),
            new CreateOrder($this->companyId),
        ];
    }
}
```

### Archivos involucrados

| Capa          | Ruta                                                  |
| ------------- | ----------------------------------------------------- |
| Agente        | `app/Ai/Agents/SalesAgent.php`                        |
| Prompt        | `resources/ai/prompts/agent-ventas.md`                |
| Tool          | `app/Ai/Tools/GetProducts.php`                        |
| Tool          | `app/Ai/Tools/GetCustomerByPhone.php`                 |
| Tool          | `app/Ai/Tools/CreateCustomer.php`                     |
| Tool          | `app/Ai/Tools/CreateCustomerAddress.php`              |
| Tool          | `app/Ai/Tools/CreateOrder.php`                        |

### Consideraciones técnicas

- `company_id` se pasa al agente vía constructor (`SalesAgent::make(companyId: $companyId)`); nunca como entrada del usuario.
- El trait `RemembersConversations` requiere que las migraciones del AI SDK estén corridas antes de usar el agente.
- Las tools de escritura (`create_customer`, `create_customer_address`, `create_order`) deben estar implementadas antes de desplegar el agente en producción.
- Si alguna tool no está disponible aún, el agente puede correr sin ella omitiendo su instancia en `tools()`; el prompt debe actualizarse acorde.
- No se aplica output estructurado (`HasStructuredOutput`) en este agente; la respuesta final es texto conversacional.
- El agente no necesita policy/gate interna; la autorización debe ocurrir en el controlador o middleware que instancia el agente.
