---
description: CRUD de clientes del sistema Ventas por empresa (sesión), con nombre completo y teléfono obligatorio; teléfono único por compañía entre registros no eliminados; baja lógica (SoftDeletes)
type: negocio
date: 2026-05-13
status: draft
user: —
---

# CRUD de clientes (Ventas)

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Permitir administrar **clientes** asociados a la empresa en contexto (`company_selected`), con datos mínimos de contacto: **nombre completo** y **teléfono**. El teléfono no puede repetirse **entre clientes activos (no eliminados) de la misma empresa**; un cliente **eliminado en baja lógica** deja de aparecer en listados y edición, pero el registro permanece en base de datos. Tras una baja lógica, **se puede volver a dar de alta** otro cliente con el mismo teléfono en esa empresa. Otra compañía puede usar el mismo número con independencia de bajas lógicas.

El alcance funcional previsto en interfaz es **listar** clientes y **editar** los existentes; los permisos CRUD completos cubren también alta y **baja lógica** según rol (p. ej. botón “Nuevo cliente” y eliminar solo si el rol incluye `create` / `delete`).

### Actores y roles

| Actor                   | Acceso / comportamiento                                                                                                |
| ----------------------- | ---------------------------------------------------------------------------------------------------------------------- |
| root                    | Acceso total; no depende de permisos granulares.                                                                       |
| owner / usuario con rol | Solo acciones permitidas por los permisos `sales.customers.*` asignados a su rol (listar, crear, actualizar, eliminar). |
| sin permiso             | No ve la entrada de menú ni puede invocar rutas del módulo (middleware + policy).                                      |

**Contexto de empresa:** el usuario debe tener empresa seleccionada cuando aplique el flujo multi-empresa (misma convención que Stock: `EnsureCompanySelected` + sesión `company_selected`, ver `App\Support\SelectedCompanySession`).

### Flujo principal

1. El usuario con permiso **Listar** y empresa en contexto abre el menú bajo el grupo **Ventas** y entra a **Clientes**.
2. Ve el listado **solo de clientes activos (no eliminados en baja lógica) de la empresa seleccionada**, con paginación y, según diseño, búsqueda u ordenación básicos.
3. Con permiso **Actualizar**, desde el listado abre la **pantalla de edición** de un cliente y guarda cambios en `full_name` y/o `phone` (respetando unicidad de teléfono frente a otros clientes **no eliminados** de la empresa).
4. Con permiso **Crear**, puede registrar un cliente nuevo (misma pantalla de formulario que edición o flujo equivalente alineado al proyecto), quedando siempre asociado a la empresa de sesión.
5. Con permiso **Eliminar**, ejecuta **baja lógica** del cliente (`deleted_at`); el registro deja de mostrarse en listados y rutas de edición/actualización; no se borra físicamente salvo operaciones administrativas futuras de “forzar borrado” (fuera de alcance si no existen).

### Reglas de negocio

- Todo cliente pertenece a **una empresa** (`company_id`); al crear, `company_id` es siempre el de **sesión**; listados, edición y eliminación lógica operan **solo** sobre clientes de esa empresa (y solo **no eliminados** en vistas normales).
- `full_name` es obligatorio.
- `phone` es obligatorio y **único por empresa entre clientes no eliminados** (reglas de validación y modelo de datos sin `UNIQUE` global que impida reutilizar teléfono tras baja lógica).
- Rutas protegidas por permisos del módulo **Clientes** del sistema **Ventas**; el ítem de menú requiere `sales.customers.list`.

### Datos que maneja el usuario

| Campo          | Tipo visible | Obligatorio | Notas                                                |
| -------------- | ------------ | ----------- | ---------------------------------------------------- |
| Empresa        | Contexto     | Sí          | No editable por el usuario; viene de sesión.         |
| Nombre completo | Texto       | Sí          | Identificación del contacto/cliente.                 |
| Teléfono       | Texto        | Sí          | Único **por empresa** entre activos; reutilizable tras baja lógica. |

### Referencias visuales

<!-- No aplica en esta versión del spec. -->

---

## ESPECIFICACIÓN TÉCNICA

### Sistema, módulo, permisos y menú

| Concepto                                               | Valor                                                                                                                                                                                                                                                                   |
| ------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Sistema (nombre)                                       | Ventas                                                                                                                                                                                                                                                                  |
| Sistema (`slug`)                                       | `sales`                                                                                                                                                                                                                                                                 |
| Módulo (nombre)                                        | Clientes                                                                                                                                                                                                                                                                |
| Segmento de módulo (`module_slug` relativo al sistema) | `customers`                                                                                                                                                                                                                                                             |
| Slug persistido del módulo                             | `sales.customers` (prefijo sistema + segmento, alineado a `Module::composeStoredSlug` / `Permission::composeStoredSlug`)                                                                                                                                              |
| Permisos CRUD (segmento → slug completo)               | `list` → `sales.customers.list`, `create` → `sales.customers.create`, `update` → `sales.customers.update`, `delete` → `sales.customers.delete`                                                                                                                        |
| Nombres sugeridos en seeder                            | Listar clientes, Crear cliente, Actualizar cliente, Eliminar cliente (o variantes cortas Listar, Crear, …)                                                                                                                                                              |
| **Alcance del menú (`menu scope`)**                    | **`Ventas`**: agrupar la entrada **Clientes** bajo un padre de navegación asociado al sistema Ventas (`sales`). La hoja del menú declara `permission: 'sales.customers.list'`.                                                                                          |

### Contexto de empresa (sesión)

| Elemento          | Uso en clientes                                                                 |
| ----------------- | ------------------------------------------------------------------------------- |
| Sesión            | Clave `company_selected` (payload con `id` de empresa), compartida vía Inertia.   |
| Resolución de ID  | `SelectedCompanySession::selectedCompanyId($request)` para el UUID en contexto. |
| Middleware        | Rutas del CRUD en grupo con `EnsureCompanySelected` (o stack equivalente).      |
| Asignación en alta | `company_id` = ID de sesión; **no** confiar en `company_id` enviado por el cliente (ignorar o sobrescribir). |
| Consultas         | Filtrar `where('company_id', $companyId)`; el modelo usa **SoftDeletes**, por lo que los listados normales excluyen `deleted_at` no nulo. |

### Relaciones

| Modelo / entidad | Relación        | Notas                                      |
| ---------------- | --------------- | ------------------------------------------ |
| `Customer`       | `belongsTo`     | `Company` vía `company_id` (FK obligatoria). |

### Modelo de datos

```
tabla: sales_customers

- id: uuid, PK
- company_id: uuid, NOT NULL, FK → configuration_companies, index
- full_name: string, NOT NULL
- phone: string, NOT NULL
- deleted_at: timestamp, nullable (SoftDeletes — baja lógica)
- created_at / updated_at: timestamps

Índices:
- INDEX (company_id)
- INDEX (company_id, phone) — búsqueda por tenant y teléfono; la unicidad “por empresa entre activos” se aplica en validación (no hay UNIQUE compuesto en BD, para permitir reutilizar `phone` tras soft delete).

Índice adicional:
- INDEX (deleted_at) — consultas con SoftDeletes.
```

### Validaciones

| Campo        | Reglas                                                                                         |
| ------------ | ---------------------------------------------------------------------------------------------- |
| `company_id` | En **store**: solo desde sesión, obligatorio implícito. En **update**: inmutable; debe coincidir con sesión y con el modelo. |
| `full_name`  | obligatorio, string, max razonable (p. ej. 255)                                              |
| `phone`      | obligatorio, string, max razonable (p. ej. 40); **Rule::unique** sobre `sales_customers.phone` con `where(company_id)` y **`whereNull(deleted_at)`**; en update, **ignore** del registro actual |

### Rutas y acciones sugeridas

| Recurso / acción | Notas                                                                 |
| ---------------- | --------------------------------------------------------------------- |
| `index`          | Listado paginado filtrado por `company_id` de sesión (solo no eliminados). |
| `create` / `store` | Alta; policy `create`; `company_id` desde sesión.                   |
| `edit` / `update` | Edición; policy `update`; mismo formulario que creación si el proyecto unifica; **model binding** solo resuelve clientes no eliminados de la empresa (404 si está en baja lógica). |
| `destroy`        | **Baja lógica** (`$customer->delete()` con `SoftDeletes`); policy `delete`. |

**Interfaces de usuario previstas (Inertia):**

- **Listar:** `resources/js/pages/sales/customers/index.tsx` — tabla, acciones según permisos (editar, eliminar, nuevo).
- **Editar:** `resources/js/pages/sales/customers/form.tsx` (o `edit.tsx`) — formulario para **actualizar** cliente existente; si se desea alta en UI, reutilizar el mismo formulario en modo creación cuando el usuario tenga `sales.customers.create` (opcional respecto al título “solo editar”, pero coherente con permisos CRUD).

### Archivos involucrados

| Capa                           | Ruta                                                                                                                                                                            |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Rutas                          | `routes/sales.php`; grupo con `auth` + empresa seleccionada.                                                                                                                    |
| Controlador                    | `app/Http/Controllers/Sales/CustomersController.php`                                                                                                                            |
| Actions                        | `app/Actions/Sales/Customers/` (listar, crear, actualizar, eliminar lógico)                                                                                                     |
| Policy                         | `app/Policies/Sales/CustomerPolicy.php` — `company_id` vs sesión en todas las abilities                                                                                         |
| Form Request                   | `StoreCustomerRequest`, `UpdateCustomerRequest` (unicidad `phone` por empresa **solo filas no eliminadas**)                                                                     |
| Modelo                         | `app/Models/Sales/Customer.php` — `SoftDeletes`, `belongsTo(Company::class)`                                                                                                   |
| Migración                      | Creación de tabla + migración que añade `deleted_at`, quita `UNIQUE(company_id, phone)` y añade índices compuestos/no únicos según convención del proyecto                      |
| Seeder sistema/módulo/permisos | Extender seeder de sistemas/módulos/permisos con **Ventas** → **Clientes** y permisos CRUD                                                                                      |
| Seeder datos                   | `database/seeders/Sales/CustomerSeeder.php` (opcional): clientes de prueba para una `Company` de referencia                                                                     |
| Vista listado                  | `resources/js/pages/sales/customers/index.tsx`                                                                                                                                  |
| Vista formulario edición       | `resources/js/pages/sales/customers/form.tsx` (o convención local del repo)                                                                                                    |
| Navegación                     | `resources/js/nav.ts`: grupo **Ventas** (`title` legible para usuarios), hijo **Clientes** con `permission: 'sales.customers.list'`                                              |

### Seeders

- Registrar **System** `sales` / “Ventas”, **Module** `sales.customers` / “Clientes” y los cuatro **Permission** `sales.customers.{list,create,update,delete}`.
- Datos de prueba opcionales: varios `Customer` con `full_name` y `phone` únicos por `company_id` en `Sales\\CustomerSeeder`.

### Elementos de frontend

- [ ] Filtros (opcional según producto)
- [x] Búsqueda sugerida por nombre o teléfono dentro de la empresa actual
- [x] Ordenación (p. ej. `full_name`, `created_at`)
- [x] Paginación
- [ ] Ocultar columnas (opcional; no requerido en v1)

### Consideraciones técnicas

- **Autorización y multi-tenant:** toda query debe incluir `company_id` de `SelectedCompanySession::selectedCompanyId`; el policy debe denegar si el cliente no pertenece a la empresa en sesión.
- **`SoftDeletes`:** el modelo `Customer` usa `Illuminate\Database\Eloquent\SoftDeletes`; listados por defecto **sin** trashed; `resolveRouteBinding` no debe resolver clientes eliminados lógicamente (comportamiento por defecto de Eloquent).
- **Unicidad de teléfono:** sin restricción `UNIQUE` en BD sobre `(company_id, phone)` (o equivalente que bloquee filas con `deleted_at`); unicidad entre **no eliminados** vía `Rule::unique` + `whereNull('deleted_at')` en store/update.
- Registrar `CustomerPolicy` y rutas; ejecutar generación de Wayfinder tras definir rutas.
- Tests sugeridos: listado solo de la empresa en sesión; no crear/actualizar con `company_id` ajeno; duplicar `phone` con otro cliente **activo** misma empresa falla; **reutilizar `phone` tras soft delete** en la misma empresa es válido; otra empresa puede reutilizar el mismo `phone` con clientes activos.
