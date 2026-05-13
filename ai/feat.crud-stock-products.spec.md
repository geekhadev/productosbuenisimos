---
description: CRUD de productos del sistema Stock por empresa (sesión), baja lógica, unicidad por compañía y tabla con desactivación y columnas configurables
type: negocio
date: 2026-05-13
status: draft
user: —
---

# CRUD de productos (Stock)

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Permitir administrar el catálogo de productos del inventario **por la empresa actualmente seleccionada en sesión** (`company_selected`). Incluye alta, listado, edición, **desactivación rápida desde la tabla** y **baja lógica** (el registro deja de mostrarse en listados normales pero permanece en base de datos con `deleted_at`). Los identificadores de negocio (`name`, `code`, `sku`) son únicos **dentro de la misma empresa**, no globalmente entre compañías.

### Actores y roles

| Actor                   | Acceso / comportamiento                                                                                                |
| ----------------------- | ---------------------------------------------------------------------------------------------------------------------- |
| root                    | Acceso total; no depende de permisos granulares.                                                                       |
| owner / usuario con rol | Solo acciones permitidas por los permisos `stock.products.*` asignados a su rol (listar, crear, actualizar, eliminar). |
| sin permiso             | No ve la entrada de menú ni puede invocar rutas del módulo (middleware + policy).                                      |

**Contexto de empresa:** el usuario debe tener empresa seleccionada cuando aplique el flujo multi-empresa del producto (misma regla que el resto del backoffice: `EnsureCompanySelected` + sesión `company_selected`, ver `App\Support\SelectedCompanySession`).

### Flujo principal

1. El usuario con permiso **Listar** y empresa en contexto abre el menú bajo el grupo **Stock** y entra a **Productos**.
2. Ve el listado **solo de productos de la empresa seleccionada**, paginado, con **filtro por estado activo/inactivo**, búsqueda y ordenación; puede **mostrar u ocultar columnas** (preferencia persistida en el cliente).
3. Con **Crear** abre el formulario; al guardar, el producto queda asociado a la empresa de sesión; se valida unicidad de `name`, `code` y `sku` **en esa empresa**.
4. Con **Actualizar** edita un registro existente **solo si el producto sigue activo** (`is_active === true`); las reglas de unicidad aplican **por empresa** ignorando el propio registro. Un producto **inactivo no admite edición** (ni por formulario ni por `PUT`/`PATCH` de actualización): la UI no muestra la acción editar y el backend responde no autorizado si se fuerza la ruta.
5. Desde la tabla, con **Desactivar** (acción dedicada), pasa `is_active` a `false` sin borrar el registro. Requiere permiso `stock.products.update` y producto aún activo; la policy define la ability **`deactivate`** (separada de **`update`**) para autorizar esta acción sin relajar la regla de “solo editar si activo”.
6. Con **Eliminar** (baja lógica) solo puede confirmar si el producto está **inactivo** (`is_active === false`); si está activo, el sistema rechaza la operación e indica que primero debe desactivarse.

### Reglas de negocio

- Todo producto pertenece a **una empresa** (`company_id`), siempre la **empresa seleccionada en sesión** al crear; listados, edición, desactivación y eliminación lógica operan **solo** sobre productos de esa empresa.
- `name`, `code` y `sku` son obligatorios y **únicos por empresa** (la misma tripleta puede repetirse en otra compañía).
- `width`, `length`, `height`, `volume`, `weight`, `minimum_stock` y `price` son opcionales en captura; si no se envían, persisten con valor **0**.
- `is_active` por defecto es **true**; los inactivos deben distinguirse visualmente (badge, filtro, etc.).
- **Eliminación:** es **lógica** (`SoftDeletes`). No se puede ejecutar **soft delete** si `is_active` es `true`; el usuario debe **desactivar** primero (botón en tabla o formulario).
- **Desactivar** no implica eliminar: el registro sigue visible según el filtro de “inactivos”. **No se puede reactivar** desde el formulario de edición mientras la regla “solo editar si activo” esté vigente; una reactivación explícita requeriría otra acción de negocio (p. ej. endpoint “activar”) fuera del alcance actual.
- **Edición / actualización** (`edit`, `update`) solo con producto **activo**; producto **inactivo**: sin botón editar en tabla, rutas de edición/actualización denegadas en policy.
- Rutas protegidas por permisos del módulo **Productos** del sistema **Stock**; el ítem de menú requiere `stock.products.list`.

### Datos que maneja el usuario

| Campo        | Tipo visible        | Obligatorio | Notas                                                         |
| ------------ | ------------------- | ----------- | ------------------------------------------------------------- |
| Nombre       | Texto               | Sí          | Único **por empresa**.                                        |
| Código       | Texto               | Sí          | Único **por empresa**. Identificador interno de negocio.      |
| SKU          | Texto               | Sí          | Único **por empresa**. Clave de almacén/comercial.            |
| Ancho        | Número              | No          | Por defecto 0.                                                |
| Largo        | Número              | No          | Por defecto 0.                                                |
| Alto         | Número              | No          | Por defecto 0.                                                |
| Volumen      | Número              | No          | Por defecto 0.                                                |
| Peso         | Número              | No          | Por defecto 0.                                                |
| Stock mínimo | Número              | No          | Por defecto 0.                                                |
| Precio       | Moneda / decimal    | No          | Por defecto 0.                                                |
| Activo       | Interruptor (sí/no) | No          | Por defecto sí (`true`). En tabla: badge de estado y acción **Desactivar** (solo si aún activo).   |

### Referencias visuales

<!-- No aplica en esta versión del spec. -->

---

## ESPECIFICACIÓN TÉCNICA

### Sistema, módulo, permisos y menú

| Concepto                                               | Valor                                                                                                                                                                                                                                                                   |
| ------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Sistema (nombre)                                       | Stock                                                                                                                                                                                                                                                                   |
| Sistema (`slug`)                                       | `stock`                                                                                                                                                                                                                                                                 |
| Módulo (nombre)                                        | Productos                                                                                                                                                                                                                                                               |
| Segmento de módulo (`module_slug` relativo al sistema) | `products`                                                                                                                                                                                                                                                              |
| Slug persistido del módulo                             | `stock.products` (prefijo sistema + segmento, igual que `Module::composeStoredSlug`)                                                                                                                                                                                    |
| Permisos CRUD (segmento → slug completo)               | `list` → `stock.products.list`, `create` → `stock.products.create`, `update` → `stock.products.update`, `delete` → `stock.products.delete`                                                                                                                              |
| Nombres sugeridos en seeder                            | Listar, Crear, Actualizar, Eliminar (o variantes “Listar productos”, … si se alinea con módulos como Compartido)                                                                                                                                                        |
| **Alcance del menú (`menu scope`)**                    | `stock`: agrupar la entrada **Productos** bajo un padre de navegación asociado al sistema Stock. La hoja del menú declara `permission: 'stock.products.list'`.                                                                                                            |

### Contexto de empresa (sesión)

| Elemento | Uso en productos |
| -------- | ---------------- |
| Sesión   | Clave `company_selected` (payload con `id` de empresa), ya compartida vía Inertia como `company_selected`. |
| Resolución de ID | `SelectedCompanySession::selectedCompanyId($request)` para obtener el UUID de la empresa en contexto. |
| Middleware | Rutas del CRUD deben pasar por el mismo criterio que el resto del área que exige empresa elegida (p. ej. `EnsureCompanySelected` en el grupo de rutas Stock), de modo que no se liste ni muten productos sin compañía válida. |
| Asignación en alta | `company_id` = ID de sesión; **no** confiar en `company_id` enviado por el cliente en el body (ignorar o sobrescribir). |
| Consultas | Filtrar siempre `where('company_id', $companyId)` (o **global scope** en el modelo `Product` aplicado desde resolver/middleware que fije el ID actual) para evitar fugas entre empresas. |

### Modelo de datos

```
tabla: stock_products (convención sugerida; ajustar si el proyecto usa otro prefijo)

- id: uuid, PK
- company_id: uuid, NOT NULL, FK → companies (o tabla equivalente), index
- name: string, NOT NULL
- code: string, NOT NULL
- sku: string, NOT NULL
- width: decimal o unsigned integer según estándar del proyecto, NOT NULL, default 0
- length: idem, NOT NULL, default 0
- height: idem, NOT NULL, default 0
- volume: idem, NOT NULL, default 0
- weight: idem, NOT NULL, default 0
- minimum_stock: idem, NOT NULL, default 0
- price: decimal(p,s), NOT NULL, default 0
- is_active: boolean, NOT NULL, default true
- deleted_at: timestamp, nullable (SoftDeletes — baja lógica)
- created_at / updated_at: timestamps

Índices únicos compuestos (ajustar nombres a convención del proyecto):
- UNIQUE (company_id, name)
- UNIQUE (company_id, code)
- UNIQUE (company_id, sku)
```

### Validaciones

| Campo           | Reglas                                                                                                                                 |
| --------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| `company_id`  | En **store**: derivado solo de sesión, obligatorio implícito. En **update**: inmutable; debe coincidir con el de sesión y con el del modelo. |
| `name`          | obligatorio, string, max razonable (p. ej. 255), único en `stock_products` **scoped por `company_id`**                               |
| `code`          | obligatorio, string, max razonable, único **scoped por `company_id`**                                                                |
| `sku`           | obligatorio, string, max razonable, único **scoped por `company_id`**                                                                |
| `width`         | a veces ausente en request: nullable o `sometimes`; default 0; si presente: numérico ≥ 0                                               |
| `length`        | igual que `width`                                                                                                                      |
| `height`        | igual que `width`                                                                                                                      |
| `volume`        | igual que `width`                                                                                                                      |
| `weight`        | igual que `width`                                                                                                                      |
| `minimum_stock` | igual que `width` (entero si aplica)                                                                                                   |
| `price`         | opcional en formulario; numérico ≥ 0; default 0                                                                                        |
| `is_active`     | boolean, opcional; default `true`                                                                                                    |

En **actualización**, reglas `unique` para `name`, `code` y `sku` deben incluir `where('company_id', …)` e **ignorar el `id` del registro actual**.

**Eliminación (destroy):** si `is_active === true`, responder **422** (o **403** según convención del API) con mensaje claro: debe desactivarse primero. Solo entonces aplicar `$product->delete()` (soft delete).

### Rutas y acciones sugeridas

| Acción | Notas |
| ------ | ----- |
| `PATCH` …`/products/{product}/deactivate` | Pone `is_active = false`. Policy: **`deactivate`** (permiso `stock.products.update` + empresa en sesión + producto aún activo). Ocultar botón si ya está inactivo. |
| `GET` …`/products/{product}/edit` y `PUT`/`PATCH` …`/products/{product}` | Solo si producto **activo**; policy **`update`** exige `is_active === true` además de permiso y empresa. |
| `destroy` (resource) | Soft delete; policy **`delete`** exige inactivo + permiso + empresa. |

**Orden de columnas en el listado (tabla):** Nombre, Código, SKU, Precio, Ancho, Largo, Alto, Volumen, Peso, Stock mín., Estado, Acciones (columna fija al final).

### Archivos involucrados

| Capa                           | Ruta                                                                                                                                                                            |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Rutas                          | `routes/stock.php`; grupo con `auth`, empresa seleccionada (`EnsureCompanySelected` o stack equivalente).                                                                        |
| Controlador                    | `app/Http/Controllers/Stock/ProductsController.php` (+ método `deactivate` si no se usa solo `update`)                                                                         |
| Actions                        | `app/Actions/Stock/Products/` (listar con filtros, crear, actualizar, desactivar, eliminar lógico)                                                                               |
| Policy                         | `app/Policies/Stock/ProductPolicy.php` — `company_id` vs sesión; `update` solo si activo; **`deactivate`** para ruta de desactivación; `delete` solo si inactivo                                                                           |
| Form Request                   | `StoreProductRequest`, `UpdateProductRequest` (unicidad por empresa); opcional `DeactivateProductRequest` vacío o con reglas mínimas                                           |
| Modelo                         | `app/Models/Stock/Product.php` — `SoftDeletes`, relación `belongsTo(Company::class)`, **global scope** o trait que acote por `company_id` de sesión (documentar en el modelo). |
| Migración                      | `database/migrations/YYYY_MM_DD_HHMMSS_create_stock_products_table.php` — FK, únicos compuestos, `deleted_at`                                                                      |
| Seeder sistema/módulo/permisos | Extender `PermissionsSeeder` (o seeder dedicado) con Stock → Productos y permisos CRUD                                                                                           |
| Seeder datos                   | `database/seeders/Stock/ProductSeeder.php` (o nombre acordado): **10 productos de prueba** por una empresa de referencia (ver siguiente sección).                               |
| Vista listado                  | `resources/js/pages/stock/products/index.tsx` — columnas en orden de negocio (Nombre…Estado), botón **Desactivar**, filtros, columnas ocultables                                                                                                     |
| Vista form                     | `resources/js/pages/stock/products/form.tsx` — alta y edición **solo en flujo de producto activo** (`edit`/`update` denegados si inactivo)                                                                                                              |
| Navegación                     | `resources/js/nav.ts`: grupo **Stock**, hijo Productos con `permission: 'stock.products.list'`                                                                                    |

### Seeders

- Asegurar **System** `stock` / “Stock”, **Module** `stock.products` / “Productos” y los cuatro **Permission** `stock.products.{list,create,update,delete}`.
- **Productos de prueba:** en un seeder dedicado (p. ej. `Stock\\ProductSeeder`), crear exactamente **10** registros `Product` asociados a una **misma** `Company` existente (p. ej. primera compañía creada por `CompanySeeder`, o `Company::factory()`, coherente con el orden en `DatabaseSeeder`). Variedad sugerida: mezcla de `is_active` true/false y datos realistas en `name`/`code`/`sku` **únicos dentro de esa empresa**.
- No crear productos huérfanos: siempre `company_id` válido.

### Elementos de frontend

- [x] **Filtros por estado:** control explícito (p. ej. select o tabs) **Todos | Solo activos | Solo inactivos** (query string `is_active` o `status` acordado) aplicado en backend y reflejado en la URL para compartir/enlazar.
- [x] Búsqueda (nombre, código, SKU) dentro de la empresa actual.
- [x] Ordenamiento (p. ej. `name`, `created_at`).
- [x] Paginación.
- [x] **Ocultar columnas:** UI tipo menú “Columnas” con checkboxes por columna; **persistir preferencia** (p. ej. `localStorage` claveada por usuario + empresa + página `stock-products-table`) para mantener el layout entre visitas.
- [x] **Orden de columnas fijado en negocio/UI:** Nombre → Código → SKU → Precio → Ancho → Largo → Alto → Volumen → Peso → Stock mín. → Estado → Acciones.

### Consideraciones técnicas

- `Product` debe usar **`Illuminate\Database\Eloquent\SoftDeletes`**; listados por defecto **sin** trashed salvo pantalla/admin futura de “papelera”.
- **Autorización y multi-tenant:** toda query debe incluir `company_id` de `SelectedCompanySession::selectedCompanyId`; el policy debe fallar si el producto no pertenece a la empresa en sesión.
- **`update` vs `deactivate`:** `ProductPolicy::update` exige `is_active` para proteger formulario y `PATCH` de producto; `ProductPolicy::deactivate` autoriza la baja de bandera solo con producto aún activo y mismo permiso de actualización en empresa, evitando el bloqueo lógico de “necesito update para desactivar pero update exige activo”.
- Índices únicos compuestos `(company_id, name|code|sku)` en migración; alinear validación `Rule::unique` con el mismo scope.
- Registrar `ProductPolicy` y rutas Wayfinder; tras definir rutas, ejecutar generación de Wayfinder.
- Tests sugeridos: no eliminar si activo; unicidad duplicada misma empresa falla; otra empresa puede reutilizar mismo SKU; desactivar vía endpoint; filtro de inactivos devuelve solo inactivos.
