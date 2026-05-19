---
description: UI para configurar el Agente de Ventas por empresa — activar/desactivar tools y editar el prompt desde el panel
type: técnico
date: 2026-05-18
status: draft
user: Irwing Naranjo
---

# Configuración del Agente de Ventas

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Permitir que usuarios autorizados configuren el comportamiento del **Agente de Ventas** desde el panel de administración, sin tocar código. La configuración es **por empresa** (la seleccionada en sesión) y comprende dos aspectos:

1. **Tools habilitadas**: qué herramientas puede invocar el agente (cada tool puede activarse o desactivarse individualmente).
2. **Prompt del agente**: el texto de instrucciones que define el comportamiento del agente, editable desde la interfaz en lugar de modificar el archivo en disco.

Si una company no tiene configuración guardada, el agente usa los **valores predeterminados**: todas las tools activas y el prompt del archivo `resources/ai/prompts/agent-ventas.md`.

### Actores y roles

| Actor                        | Acceso / comportamiento                                                                                         |
| ---------------------------- | --------------------------------------------------------------------------------------------------------------- |
| root                         | Acceso total; puede ver y modificar la configuración de cualquier empresa seleccionada.                         |
| owner / usuario con `update` | Puede ver la configuración actual y guardar cambios (`sales.agent.update`).                                     |
| usuario con `list` solamente | Puede ver la configuración actual pero no editarla (lectura).                                                   |
| sin permiso                  | No ve la entrada de menú ni puede acceder a las rutas del módulo.                                               |

### Flujo principal

1. El usuario con permiso `sales.agent.list` abre el menú bajo **Ventas** y accede a **Agente de Ventas**.
2. Ve la pantalla de configuración con:
   - Una sección **Herramientas** con un toggle por cada tool disponible, indicando nombre legible y descripción corta.
   - Una sección **Instrucciones del agente** con un editor de texto que muestra el prompt actual (guardado en BD o el predeterminado si no hay configuración).
3. Con permiso `sales.agent.update`, el usuario activa/desactiva tools y/o edita el prompt.
4. Pulsa **Guardar**; los cambios se persisten en la tabla `sales_agent_configs` para la empresa en sesión.
5. El Agente de Ventas, al instanciarse, lee la configuración de la empresa y aplica las tools habilitadas y el prompt guardado.

### Reglas de negocio

- La configuración es **por empresa** (`company_id` de sesión); nunca se puede alterar la config de otra empresa desde esta UI.
- Si no existe fila en `sales_agent_configs` para la empresa, el agente usa todos los defaults (todas las tools ON, prompt del archivo en disco).
- Al guardar, se hace **upsert** de la fila de configuración (una sola fila por empresa).
- El prompt guardado en BD **reemplaza completamente** el archivo por defecto; si el campo está vacío, el agente vuelve al archivo.
- Deshabilitar una tool solo la excluye del array `tools()` del agente; no elimina datos ni afecta pedidos ya creados.
- No se permite deshabilitar **todas** las tools simultáneamente (debe quedar al menos una activa).
- El menú muestra la entrada solo si el usuario tiene `sales.agent.list`.

### Datos que maneja el usuario

| Campo              | Tipo visible           | Obligatorio | Notas                                                                             |
| ------------------ | ---------------------- | ----------- | --------------------------------------------------------------------------------- |
| Tool: get_products | Toggle (on/off)        | —           | "Consultar catálogo de productos"                                                 |
| Tool: get_customer_by_phone | Toggle      | —           | "Buscar cliente por teléfono"                                                    |
| Tool: create_customer | Toggle             | —           | "Crear cliente nuevo"                                                             |
| Tool: create_customer_address | Toggle     | —           | "Agregar dirección de entrega"                                                   |
| Tool: create_order | Toggle                 | —           | "Registrar pedido"                                                                |
| Prompt             | Textarea / code editor | No          | Instrucciones del agente en Markdown. Si se deja vacío, se usa el predeterminado. |

### Referencias visuales

<!-- Wireframe: una sola página dividida en dos secciones verticales.
     Sección superior: card "Herramientas" con lista de toggles (label + descripción corta a la derecha).
     Sección inferior: card "Instrucciones del agente" con textarea de alto fijo y botón "Restaurar predeterminado".
     Footer sticky: botón "Guardar cambios" (deshabilitado si sin permiso update). -->

---

## ESPECIFICACIÓN TÉCNICA

### Sistema, módulo, permisos y menú

| Concepto                      | Valor                                                                                                    |
| ----------------------------- | -------------------------------------------------------------------------------------------------------- |
| Sistema (nombre)              | Ventas                                                                                                   |
| Sistema (`slug`)              | `sales`                                                                                                  |
| Módulo (nombre)               | Agente de Ventas                                                                                         |
| Segmento de módulo            | `agent`                                                                                                  |
| Slug persistido del módulo    | `sales.agent`                                                                                            |
| Permisos                      | `list` → `sales.agent.list`, `update` → `sales.agent.update`                                            |
| Nombres en seeder             | Listar Agente de Ventas, Actualizar Agente de Ventas                                                    |
| Menú (scope)                  | Grupo **Ventas**; hijo **Agente de Ventas** con `permission: 'sales.agent.list'`                        |

> No se crean permisos `create` ni `delete` porque la configuración es un **singleton por empresa** (upsert).

### Modelo de datos

```
tabla: sales_agent_configs

- id:              uuid, PK
- company_id:      uuid, NOT NULL, FK → configuration_companies, UNIQUE (una config por empresa)
- enabled_tools:   json, NOT NULL, default: ["get_products","get_customer_by_phone","create_customer","create_customer_address","create_order"]
  -- array de slugs de tools habilitadas; el agente solo instancia las que están en este array
- prompt:          text, nullable
  -- null = usar archivo resources/ai/prompts/agent-ventas.md; cualquier otro valor = instrucciones custom
- created_at / updated_at: timestamps

Índices:
- UNIQUE (company_id)
```

### Validaciones

| Campo           | Reglas                                                                                                                                             |
| --------------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| `company_id`    | Solo desde sesión; no acepto del request; inmutable post-creación.                                                                                 |
| `enabled_tools` | array, min 1 elemento, cada elemento debe ser uno de los slugs válidos: `get_products`, `get_customer_by_phone`, `create_customer`, `create_customer_address`, `create_order`. |
| `prompt`        | nullable, string, max: 10000.                                                                                                                      |

### Cambios en `SalesAgent`

El agente debe leer la configuración de la empresa antes de exponer tools e instrucciones:

```php
public function instructions(): Stringable|string
{
    $config = SalesAgentConfig::forCompany($this->companyId);

    if ($config && filled($config->prompt)) {
        return $config->prompt;
    }

    return file_get_contents(resource_path('ai/prompts/agent-ventas.md'));
}

public function tools(): iterable
{
    $config  = SalesAgentConfig::forCompany($this->companyId);
    $enabled = $config?->enabled_tools ?? $this->defaultTools();

    $allTools = [
        'get_products'            => new GetProducts($this->companyId),
        'get_customer_by_phone'   => new GetCustomerByPhone($this->companyId),
        'create_customer'         => new CreateCustomer($this->companyId),
        'create_customer_address' => new CreateCustomerAddress($this->companyId),
        'create_order'            => new CreateOrder($this->companyId),
    ];

    return array_values(array_intersect_key($allTools, array_flip($enabled)));
}

private function defaultTools(): array
{
    return array_keys([
        'get_products', 'get_customer_by_phone',
        'create_customer', 'create_customer_address', 'create_order',
    ]);
}
```

### Rutas y controlador

Dos rutas bajo el grupo `auth` + `EnsureCompanySelected`:

| Verbo | URI                        | Acción del controlador     | Policy ability |
| ----- | -------------------------- | -------------------------- | -------------- |
| GET   | `/sales/agent`             | `AgentConfigController@edit` | `view`       |
| PUT   | `/sales/agent`             | `AgentConfigController@update` | `update`   |

No se usa resource completo; solo `edit` (GET) y `update` (PUT).

### Archivos involucrados

| Capa              | Ruta                                                                              |
| ----------------- | --------------------------------------------------------------------------------- |
| Agente (modificar)| `app/Ai/Agents/SalesAgent.php`                                                    |
| Modelo config     | `app/Models/Sales/SalesAgentConfig.php`                                           |
| Migración         | `database/migrations/YYYY_MM_DD_000000_create_sales_agent_configs_table.php`      |
| Controlador       | `app/Http/Controllers/Sales/AgentConfigController.php`                            |
| Action update     | `app/Actions/Sales/AgentConfig/UpdateAgentConfig.php`                             |
| Policy            | `app/Policies/Sales/AgentConfigPolicy.php`                                        |
| Form Request      | `app/Http/Requests/Sales/UpdateAgentConfigRequest.php`                            |
| Rutas             | `routes/sales.php` (agregar las dos rutas al grupo existente)                     |
| Vista             | `resources/js/pages/sales/agent/edit.tsx`                                         |
| Seeder permisos   | `database/seeders/Administration/PermissionsSeeder.php` (agregar módulo `agent`)  |
| Menú              | `resources/js/nav.ts` (agregar entrada bajo Ventas)                               |

### Modelo `SalesAgentConfig`

```php
namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SalesAgentConfig extends Model
{
    use HasUuids;

    protected $table = 'sales_agent_configs';

    protected $fillable = ['company_id', 'enabled_tools', 'prompt'];

    protected $casts = ['enabled_tools' => 'array'];

    public static function forCompany(string $companyId): ?static
    {
        return static::where('company_id', $companyId)->first();
    }
}
```

### Policy `AgentConfigPolicy`

```php
// view   → requiere 'sales.agent.list'
// update → requiere 'sales.agent.update'
```

El policy verifica que la config (si existe) pertenece a la empresa en sesión antes de autorizar `update`.

### Seeder — extensión de `PermissionsSeeder`

Agregar al array `$structure` bajo el sistema `sales`:

```php
[
    'module_name' => 'Agente de Ventas',
    'module_slug' => 'agent',
    'permissions' => [
        ['permission_name' => 'Listar Agente de Ventas',      'permission_slug' => 'list'],
        ['permission_name' => 'Actualizar Agente de Ventas',  'permission_slug' => 'update'],
    ],
],
```

### Menú — cambio en `nav.ts`

Agregar dentro del grupo `Ventas`, después de `Pedidos`:

```ts
import { edit as agentEdit } from '@/routes/sales/agent';
import { Bot } from 'lucide-react';

// en el array items de Ventas:
{
    title: 'Agente de Ventas',
    href: agentEdit(),
    icon: Bot,
    permission: 'sales.agent.list',
},
```

### Elementos de frontend

- [ ] Filtros — No aplica (singleton)
- [ ] Búsqueda — No aplica
- [ ] Ordenamiento — No aplica
- [ ] Paginación — No aplica
- [x] Toggles para cada tool (nombre legible + descripción corta + estado on/off)
- [x] Textarea/editor para el prompt con botón "Restaurar predeterminado" (limpia el campo)
- [x] Botón "Guardar cambios" deshabilitado si el usuario no tiene `sales.agent.update`
- [x] Feedback visual al guardar (toast de éxito/error)
- [x] La página carga el prompt predeterminado del archivo cuando `config.prompt` es null

### Consideraciones técnicas

- **Upsert**: usar `SalesAgentConfig::updateOrCreate(['company_id' => $companyId], [...])` en la action.
- **Caché**: considerar cachear `SalesAgentConfig::forCompany()` por `company_id` con TTL corto (ej. 60 s) si el agente se instancia frecuentemente; invalidar al guardar.
- **Prompt predeterminado en frontend**: el controlador siempre envía el texto del prompt a renderizar — si `config->prompt` es null, envía el contenido del archivo MD leído con `file_get_contents(resource_path('ai/prompts/agent-ventas.md'))`. El frontend muestra este valor inicial en el editor pero envía `null` al backend si el usuario pulsa "Restaurar predeterminado".
- **Seguridad**: el prompt puede contener instrucciones arbitrarias; el acceso a este módulo debe restringirse a roles de confianza (owner, admin). El policy garantiza que solo usuarios con `sales.agent.update` puedan guardar.
- **Compatibilidad**: si se agrega una nueva tool en el futuro, el agente la incluirá por defecto en empresas sin config guardada; en empresas con config, la nueva tool **no** aparecerá hasta que el usuario la active desde esta UI (o se migra la columna con un default actualizado).
- **No aplica output estructurado** en `SalesAgent`; esta feature no modifica la lógica de respuesta del agente, solo su configuración de arranque.
