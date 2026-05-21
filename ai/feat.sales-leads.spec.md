---
description: CRUD de leads del sistema Ventas por empresa (sesión); captura teléfonos de personas que iniciaron conversación en el chatbot pero no son clientes aún; ciclo de vida con estados; conversión opcional a cliente existente; baja lógica
type: negocio
date: 2026-05-21
status: draft
user: Irwing Naranjo
---

# CRUD de leads (Ventas)

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Permitir administrar **leads** asociados a la empresa en contexto (`company_selected`). Un lead representa una persona que mostró interés (vía chatbot o de forma manual) pero que **no necesariamente realizó una compra**. El objetivo es que el equipo de ventas pueda hacer seguimiento de estos contactos y eventualmente convertirlos en clientes.

La diferencia con un **Customer** es que el customer existe cuando hay un pedido; el lead existe desde el primer contacto. Un lead puede estar vinculado a un `Customer` si ya compró, pero no es obligatorio.

Los leads se pueden crear automáticamente desde el chatbot (vía tool de IA) o manualmente desde el panel de administración.

### Actores y roles

| Actor                   | Acceso / comportamiento                                                                                                |
| ----------------------- | ---------------------------------------------------------------------------------------------------------------------- |
| root                    | Acceso total; no depende de permisos granulares.                                                                       |
| owner / usuario con rol | Solo acciones permitidas por los permisos `sales.leads.*` asignados a su rol (listar, crear, actualizar, eliminar).    |
| sin permiso             | No ve la entrada de menú ni puede invocar rutas del módulo.                                                            |
| Chatbot público         | Crea o recupera leads al iniciar chat (`IniciarChatbot`) o nueva conversación (`NuevaConversacionChatbot`) con el teléfono ingresado. |
| Agente IA               | No crea leads; nunca accede al CRUD directamente.                                                                      |

### Flujo principal

1. El usuario con permiso **Listar** abre el menú bajo el grupo **Ventas** y entra a **Leads**.
2. Ve el listado de leads activos de la empresa seleccionada, con paginación y búsqueda básica por teléfono.
3. Con permiso **Actualizar**, abre la pantalla de edición y puede cambiar el `status` del lead.
4. Con permiso **Crear**, puede registrar un lead manualmente ingresando teléfono y fuente (`manual`).
5. Con permiso **Eliminar**, ejecuta baja lógica del lead.

### Reglas de negocio

- Todo lead pertenece a **una empresa** (`company_id`); al crear, `company_id` es siempre el de sesión.
- `phone` es obligatorio.
- **No hay unicidad estricta de teléfono por empresa**: un mismo teléfono puede generar múltiples leads a lo largo del tiempo (p. ej. si la persona contacta en distintas ocasiones). La unicidad se gestiona en la lógica del tool de IA (no duplicar si ya existe un lead activo con ese teléfono y empresa).
- `source` indica de dónde vino el contacto: `web`, `whatsapp`, `facebook`, `instagram` o `manual`.
- `status` representa el estado del ciclo de vida del lead:
  - `nuevo` — recién registrado, sin contacto del equipo.
  - `contactado` — el equipo de ventas se puso en contacto.
  - `convertido` — el lead realizó una compra; se vincula con un `Customer` vía `customer_id`.
  - `inactivo` — no tiene intención de compra o se perdió el contacto.
- `customer_id` es **nullable**; se llena al momento de conversión (manual o automática por el agente).
- Rutas protegidas por permisos del módulo **Leads** del sistema **Ventas**; el ítem de menú requiere `sales.leads.list`.

### Datos que maneja el usuario

| Campo      | Tipo visible | Obligatorio | Notas                                                   |
| ---------- | ------------ | ----------- | ------------------------------------------------------- |
| Empresa    | Contexto     | Sí          | No editable; viene de sesión.                           |
| Teléfono   | Texto        | Sí          | Identificador principal del lead.                       |
| Fuente     | Selección    | Sí          | web / whatsapp / facebook / instagram / manual          |
| Estado     | Selección    | Sí          | nuevo / contactado / convertido / inactivo              |
| Cliente    | Relación     | No          | Nombre del customer vinculado si el lead fue convertido |

---

## ESPECIFICACIÓN TÉCNICA

### Sistema, módulo, permisos y menú

| Concepto                                               | Valor                                                                                                                                               |
| ------------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------- |
| Sistema (nombre)                                       | Ventas                                                                                                                                              |
| Sistema (`slug`)                                       | `sales`                                                                                                                                             |
| Módulo (nombre)                                        | Leads                                                                                                                                               |
| Segmento de módulo (`module_slug` relativo al sistema) | `leads`                                                                                                                                             |
| Slug persistido del módulo                             | `sales.leads`                                                                                                                                       |
| Permisos CRUD (segmento → slug completo)               | `list` → `sales.leads.list`, `create` → `sales.leads.create`, `update` → `sales.leads.update`, `delete` → `sales.leads.delete`                    |
| Nombres sugeridos en seeder                            | Listar leads, Crear lead, Actualizar lead, Eliminar lead                                                                                            |
| **Alcance del menú (`menu scope`)**                    | **`Ventas`**: agrupar la entrada **Leads** bajo el padre de navegación del sistema Ventas (`sales`). La hoja del menú declara `permission: 'sales.leads.list'`. |

### Contexto de empresa (sesión)

| Elemento          | Uso en leads                                                                    |
| ----------------- | ------------------------------------------------------------------------------- |
| Sesión            | Clave `company_selected` (payload con `id` de empresa), compartida vía Inertia. |
| Resolución de ID  | `SelectedCompanySession::selectedCompanyId($request)` para el UUID en contexto. |
| Middleware        | Rutas del CRUD en grupo con `EnsureCompanySelected`.                            |
| Asignación en alta | `company_id` = ID de sesión; nunca confiar en `company_id` enviado por el cliente. |
| Consultas         | Filtrar `where('company_id', $companyId)`; excluir `deleted_at` no nulo.        |

### Relaciones

| Modelo / entidad | Relación    | Notas                                                                  |
| ---------------- | ----------- | ---------------------------------------------------------------------- |
| `Lead`           | `belongsTo` | `Company` vía `company_id` (FK obligatoria, cascade delete).           |
| `Lead`           | `belongsTo` | `Customer` vía `customer_id` (FK nullable, setNull on delete).         |

### Modelo de datos

```
tabla: sales_leads

- id:          uuid, PK
- company_id:  uuid, NOT NULL, FK → configuration_companies (cascade delete), index
- phone:       string(40), NOT NULL
- source:      string, NOT NULL  — enum: web | whatsapp | facebook | instagram | manual
- status:      string, NOT NULL, default 'nuevo'  — enum: nuevo | contactado | convertido | inactivo
- customer_id: uuid, nullable, FK → sales_customers (set null on delete), index
- deleted_at:  timestamp, nullable (SoftDeletes)
- created_at / updated_at: timestamps

Índices:
- INDEX (company_id)
- INDEX (company_id, phone)   — búsqueda por tenant y teléfono
- INDEX (company_id, status)  — filtrado por estado
- INDEX (customer_id)
```

### Enums sugeridos

```php
// app/Enums/Sales/LeadSource.php
enum LeadSource: string
{
    case Web       = 'web';
    case Whatsapp  = 'whatsapp';
    case Facebook  = 'facebook';
    case Instagram = 'instagram';
    case Manual    = 'manual';
}

// app/Enums/Sales/LeadStatus.php
enum LeadStatus: string
{
    case Nuevo      = 'nuevo';
    case Contactado = 'contactado';
    case Convertido = 'convertido';
    case Inactivo   = 'inactivo';
}
```

### Validaciones

| Campo        | Reglas                                                                                          |
| ------------ | ----------------------------------------------------------------------------------------------- |
| `company_id` | En **store**: solo desde sesión, obligatorio implícito. En **update**: inmutable.               |
| `phone`      | obligatorio, string, max:40                                                                     |
| `source`     | obligatorio, string, `in:web,whatsapp,facebook,instagram,manual`                               |
| `status`     | obligatorio, string, `in:nuevo,contactado,convertido,inactivo`                                 |
| `customer_id`| opcional, uuid, debe existir en `sales_customers` con mismo `company_id` de sesión si se envía |

### Rutas y acciones sugeridas

| Recurso / acción  | Notas                                                                           |
| ----------------- | ------------------------------------------------------------------------------- |
| `index`           | Listado paginado filtrado por `company_id` de sesión (solo no eliminados).      |
| `create` / `store`| Alta manual; policy `create`; `company_id` desde sesión; `source` default `manual`. |
| `edit` / `update` | Edición de `status` y `customer_id`; policy `update`; model binding solo resuelve leads no eliminados de la empresa. |
| `destroy`         | Baja lógica; policy `delete`.                                                   |

**Interfaces de usuario previstas (Inertia):**

- **Listar:** `resources/js/pages/sales/leads/index.tsx` — tabla con teléfono, fuente, estado, cliente vinculado (si aplica), fecha de creación; acciones según permisos.
- **Formulario:** `resources/js/pages/sales/leads/form.tsx` — formulario para creación manual y edición de estado.

### Archivos involucrados

| Capa                           | Ruta                                                                            |
| ------------------------------ | ------------------------------------------------------------------------------- |
| Rutas                          | `routes/sales.php`; grupo con `auth` + empresa seleccionada.                    |
| Controlador                    | `app/Http/Controllers/Sales/LeadsController.php`                                |
| Actions                        | `app/Actions/Sales/Leads/` (listar, crear, actualizar, eliminar lógico)         |
| Policy                         | `app/Policies/Sales/LeadPolicy.php`                                             |
| Form Request                   | `StoreLeadRequest`, `UpdateLeadRequest`                                         |
| Modelo                         | `app/Models/Sales/Lead.php` — `SoftDeletes`, `belongsTo(Company)`, `belongsTo(Customer)` |
| Enums                          | `app/Enums/Sales/LeadSource.php`, `app/Enums/Sales/LeadStatus.php`              |
| Migración                      | `create_sales_leads_table`                                                      |
| Seeder sistema/módulo/permisos | Extender seeder con **Ventas** → **Leads** y permisos CRUD                      |
| Vista listado                  | `resources/js/pages/sales/leads/index.tsx`                                      |
| Vista formulario               | `resources/js/pages/sales/leads/form.tsx`                                       |
| Navegación                     | `resources/js/nav.ts`: grupo **Ventas**, hijo **Leads** con `permission: 'sales.leads.list'` |

### Seeders

- Registrar **Module** `sales.leads` / "Leads" y los cuatro **Permission** `sales.leads.{list,create,update,delete}`.
- El System `sales` ya existe; no volver a crearlo.

### Elementos de frontend

- [x] Búsqueda por teléfono dentro de la empresa actual
- [x] Filtro por `status`
- [x] Filtro por `source`
- [x] Ordenación por `created_at`
- [x] Paginación
- [ ] Ocultar columnas (no requerido en v1)

### Consideraciones técnicas

- **Multi-tenant:** toda query debe incluir `company_id` de `SelectedCompanySession::selectedCompanyId`; el policy debe denegar si el lead no pertenece a la empresa en sesión.
- **`SoftDeletes`:** el modelo `Lead` usa `Illuminate\Database\Eloquent\SoftDeletes`; listados por defecto sin trashed.
- **`customer_id` nullable:** al mostrar en listado, hacer `with('customer')` y mostrar `customer.full_name` si existe, o "—" si no.
- **No unicidad de teléfono en BD:** la lógica de deduplicación (no crear lead duplicado para el mismo teléfono activo) se maneja en la Action del tool de IA, no en una restricción de base de datos.
- Registrar `LeadPolicy` en `AuthServiceProvider` y ejecutar generación de Wayfinder tras definir rutas.
