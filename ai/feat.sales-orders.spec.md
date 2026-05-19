---
description: Pedidos del sistema Ventas por empresa (sesión), con identificador legible único por compañía, cliente y dirección obligatorios, líneas de detalle (productos de Stock), importe total coherente con las líneas; listado y edición en interfaz; alta fuera de UI; eliminación física
type: negocio
date: 2026-05-13
status: draft
user: —
---

# Pedidos / órdenes (Ventas)

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Gestionar **pedidos (órdenes)** del módulo **Ventas**, siempre asociados a la **empresa en contexto** (`company_selected`), a un **cliente** y a una **dirección de ese cliente**, con un **nombre o referencia** de pedido, un **detalle de productos** (líneas del pedido) y un **importe total**. El nombre del pedido debe ser **único dentro de la empresa** (no puede repetirse entre pedidos de la misma compañía).

Cada pedido incluye **una o más líneas** (`OrderItem`): cada línea referencia un **producto del catálogo Stock** de la misma empresa, con **cantidad**, **precio unitario** (capturado al momento del pedido) y **subtotal de línea**. El **importe total del pedido** debe ser coherente con la suma de los subtotales de sus líneas.

En **interfaz** solo se prevén **listado** y **edición** de pedidos existentes: **no** hay flujo de **alta desde la aplicación web** (los pedidos se crean por otro canal — integración, proceso interno, consola, etc.— o queda fuera de alcance de esta UI). Los permisos del módulo siguen el esquema **CRUD** para alinear con el resto del sistema (p. ej. `create` / `delete` para procesos no-UI o evolución futura).

La **baja** de un pedido es **eliminación física** en base de datos (no hay baja lógica ni `deleted_at`).

### Actores y roles

| Actor                   | Acceso / comportamiento                                                                                                |
| ----------------------- | ---------------------------------------------------------------------------------------------------------------------- |
| root                    | Acceso total; no depende de permisos granulares.                                                                       |
| owner / usuario con rol | Solo acciones permitidas por los permisos `sales.orders.*` asignados a su rol (listar, crear, actualizar, eliminar). |
| sin permiso             | No ve la entrada de menú ni puede invocar rutas del módulo (middleware + policy).                                      |

**Contexto de empresa:** el usuario debe tener empresa seleccionada cuando aplique el flujo multi-empresa (misma convención que Stock/Ventas: `EnsureCompanySelected` + sesión `company_selected`, ver `App\Support\SelectedCompanySession`).

### Flujo principal

1. El usuario con permiso **Listar** y empresa en contexto abre el menú bajo el grupo **Ventas** y entra a **Pedidos** (o **Órdenes**, según copy acordado).
2. Ve el listado **solo de pedidos de la empresa seleccionada**, con paginación, búsqueda, ordenación, **filtro por rango de fechas** (fecha de creación del pedido) y posibilidad de **mostrar u ocultar columnas** de la tabla (preferencia persistida en el cliente).
3. Con permiso **Actualizar**, desde el listado abre la **pantalla de edición** de un pedido, revisa el **detalle de productos** (tabla de líneas) y guarda cambios en cabecera y/o líneas según alcance de la versión, respetando unicidad de `name` por empresa, coherencia **cliente ↔ dirección** y reglas de **productos ↔ empresa**.
4. **No** existe en esta versión una pantalla de **creación** de pedidos en la interfaz; el alta ocurre fuera de la UI o por otro mecanismo, siempre con `company_id` acorde a la empresa dueña del registro.
5. Con permiso **Eliminar**, puede **borrar definitivamente** el pedido de la base de datos (**eliminación física**); debe existir confirmación en UI acorde a la criticidad del dato.

### Reglas de negocio

- Todo pedido pertenece a **una empresa** (`company_id`); listados, edición y eliminación operan **solo** sobre pedidos de la empresa en sesión. En alta no-UI, `company_id` debe ser el de la empresa propietaria del registro (no confiar en valores arbitrarios del cliente si el canal es público).
- `name` es obligatorio y **único por empresa** (dos pedidos de la misma compañía no pueden compartir el mismo `name`; otra compañía sí puede reutilizar el mismo texto).
- `customer_id` es obligatorio y debe referenciar un **cliente** (`Customer`) de la **misma empresa** en contexto (o de la empresa del pedido en canales no-UI).
- `address_id` es obligatorio y debe referenciar una **dirección de cliente** (`CustomerAddress`) que pertenezca **al mismo** `customer_id` del pedido (validación de integridad referencial de negocio).
- `total_amount` es obligatorio; representa el importe total del pedido en la moneda/unidad acordada por el producto (p. ej. decimal con precisión fija en BD). Debe coincidir con la **suma de `line_total`** de todas las líneas del pedido (tolerancia cero salvo redondeo explícito documentado en implementación).
- Todo pedido tiene **al menos una línea** de detalle al persistirse (pedido sin productos no es válido en negocio).
- Cada **línea** (`OrderItem`) pertenece a **un solo pedido** (`order_id`) y referencia **un producto** (`product_id` → `stock_products`) de la **misma empresa** que el pedido.
- En **alta** de líneas (canal no-UI o evolución futura), el `unit_price` de la línea se toma del **precio vigente del producto** (`stock_products.price`) salvo que el canal envíe un precio explícito acordado por negocio; en cualquier caso el valor queda **congelado en la línea** (no se recalcula automáticamente si el catálogo cambia después).
- `quantity` por línea es obligatoria, **entera positiva** (mínimo 1).
- `line_total` = `quantity × unit_price` (persistido o recalculado en servidor antes de guardar; el cliente no es fuente de verdad del subtotal).
- Un mismo **producto** no puede repetirse en dos líneas del **mismo pedido** (índice único `order_id + product_id`); para más unidades del mismo artículo, incrementar `quantity` en la línea existente.
- Solo se pueden asociar productos **activos** (`is_active = true` y no eliminados en soft delete) al **crear o reemplazar** líneas; líneas ya guardadas pueden seguir mostrando un producto inactivo o eliminado lógicamente (solo lectura / histórico).
- Al **eliminar físicamente** un pedido, sus líneas se eliminan en cascada (no quedan huérfanas).
- **Filtro por fechas (listado):** el usuario puede acotar pedidos por **rango de fechas** sobre `created_at` (fecha de alta del pedido). Parámetros opcionales `date_from` y `date_to` (formato fecha `Y-m-d`); si solo envía uno, el otro extremo queda abierto. Si envía ambos, `date_to` debe ser **≥** `date_from`. Sin filtros de fecha, se listan todos los pedidos de la empresa (sujeto a paginación).
- Rutas protegidas por permisos del módulo **Pedidos** del sistema **Ventas**; el ítem de menú requiere `sales.orders.list`. Las líneas **no** tienen permisos ni menú propios (sub-recurso de `sales.orders`, igual que dirección respecto a clientes).

### Datos que maneja el usuario

| Campo          | Tipo visible | Obligatorio | Notas                                                                 |
| -------------- | ------------ | ----------- | --------------------------------------------------------------------- |
| Empresa        | Contexto     | Sí          | No editable por el usuario en UI; viene de sesión / contexto.         |
| Nombre / ref.  | Texto        | Sí          | Identificador legible del pedido; **único por empresa**.              |
| Cliente        | Relación     | Sí          | Selector o enlace al cliente de la empresa actual.                    |
| Dirección      | Relación     | Sí          | Solo direcciones del cliente seleccionado.                           |
| Importe total  | Número/moneda | Sí         | Total del pedido; debe reflejar la suma de líneas.                    |
| Líneas / detalle | Tabla anidada | Sí (≥1 línea al guardar pedido completo) | Producto, cantidad, precio unitario, subtotal por línea. |

**Campos visibles por línea de pedido**

| Campo           | Tipo visible   | Obligatorio | Notas                                                                 |
| --------------- | -------------- | ----------- | --------------------------------------------------------------------- |
| Producto        | Relación / selector | Sí   | Solo productos **activos** de la empresa en sesión (`stock_products`). |
| Cantidad        | Número entero  | Sí          | ≥ 1.                                                                  |
| Precio unitario | Moneda         | Sí          | Por defecto el `price` del producto al elegirlo; editable si negocio lo permite en update. |
| Subtotal línea  | Moneda (calc.) | —           | `cantidad × precio unitario`; mostrado en UI, calculado en backend.   |

**Alcance UI v1 (líneas):** en la pantalla de **edición** del pedido, sección **Detalle** con tabla de líneas (lectura de pedidos existentes). La **modificación de líneas** en UI (añadir/quitar/cambiar cantidades) queda **opcional en v1**: si no se implementa, las líneas solo se gestionan en el canal de alta no-UI y la UI las muestra en solo lectura; si se implementa, el `update` del pedido acepta un arreglo anidado de líneas y recalcula `total_amount`.

### Referencias visuales

<!-- No aplica en esta versión del spec. -->

---

## ESPECIFICACIÓN TÉCNICA

### Sistema, módulo, permisos y menú

| Concepto                                               | Valor                                                                                                                                                                                                                                                                 |
| ------------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Sistema (nombre)                                       | Ventas                                                                                                                                                                                                                                                                |
| Sistema (`slug`)                                       | `sales`                                                                                                                                                                                                                                                               |
| Módulo (nombre)                                        | Pedidos (u “Órdenes”, unificar copy en UI)                                                                                                                                                                                                                             |
| Segmento de módulo (`module_slug` relativo al sistema) | `orders`                                                                                                                                                                                                                                                              |
| Slug persistido del módulo                             | `sales.orders` (prefijo sistema + segmento, alineado a `Module::composeStoredSlug` / `Permission::composeStoredSlug`)                                                                                                                                               |
| Permisos CRUD (segmento → slug completo)               | `list` → `sales.orders.list`, `create` → `sales.orders.create`, `update` → `sales.orders.update`, `delete` → `sales.orders.delete`                                                                                                                                  |
| Nombres sugeridos en seeder                            | Listar pedidos, Crear pedido, Actualizar pedido, Eliminar pedido (o variantes cortas Listar, Crear, …)                                                                                                                                                                  |
| **Alcance del menú (`menu scope`)**                    | **`Ventas`**: agrupar la entrada del módulo bajo un padre de navegación asociado al sistema Ventas (`sales`). La hoja del menú declara `permission: 'sales.orders.list'`.                                                                                             |

### Contexto de empresa (sesión)

| Elemento           | Uso en pedidos                                                                 |
| ------------------ | ------------------------------------------------------------------------------ |
| Sesión             | Clave `company_selected` (payload con `id` de empresa), compartida vía Inertia. |
| Resolución de ID   | `SelectedCompanySession::selectedCompanyId($request)` para el UUID en contexto. |
| Middleware         | Rutas del módulo en grupo con `EnsureCompanySelected` (o stack equivalente).   |
| Asignación en alta | `company_id` = empresa propietaria del registro; en UI sin alta, aplica a APIs/procesos que creen pedidos. |
| Consultas          | Filtrar `where('company_id', $companyId)` en listados y al resolver edición/borrado. |

### Relaciones

| Modelo / entidad | Relación    | Notas                                                                 |
| ---------------- | ----------- | --------------------------------------------------------------------- |
| `Order`          | `belongsTo` | `Company` vía `company_id` (FK obligatoria).                          |
| `Order`          | `belongsTo` | `Customer` vía `customer_id` (FK obligatoria; mismo tenant que empresa). |
| `Order`          | `belongsTo` | `CustomerAddress` vía `address_id` (FK obligatoria; debe ser dirección del `customer_id`). |
| `Order`          | `hasMany`   | `OrderItem` vía `order_id` (detalle de productos; cascade al borrar pedido). |
| `OrderItem`      | `belongsTo` | `Order` vía `order_id`. |
| `OrderItem`      | `belongsTo` | `Product` (`App\Models\Stock\Product`, tabla `stock_products`) vía `product_id`. |

### Modelo de datos

```
tabla: sales_orders

- id: uuid, PK
- company_id: uuid, NOT NULL, FK → configuration_companies, index
- name: string, NOT NULL
- customer_id: uuid, NOT NULL, FK → sales_customers, index
- address_id: uuid, NOT NULL, FK → sales_customer_addresses, index
- total_amount: decimal (precisión/escala según convención del proyecto, p. ej. 12,2), NOT NULL
- created_at / updated_at: timestamps

Índices y restricciones:
- INDEX (company_id)
- INDEX (customer_id)
- INDEX (address_id)
- UNIQUE (company_id, name) — unicidad del identificador de pedido por empresa
- Sin deleted_at: no SoftDeletes; borrado = DELETE físico

tabla: sales_order_items

- id: uuid, PK
- order_id: uuid, NOT NULL, FK → sales_orders(id) ON DELETE CASCADE, index
- product_id: uuid, NOT NULL, FK → stock_products(id), index
- quantity: unsigned integer, NOT NULL (mínimo 1 en validación)
- unit_price: decimal (p. ej. 12,2), NOT NULL — precio unitario al momento del pedido
- line_total: decimal (p. ej. 12,2), NOT NULL — quantity × unit_price
- created_at / updated_at: timestamps

Índices y restricciones:
- INDEX (order_id)
- INDEX (product_id)
- UNIQUE (order_id, product_id) — un producto como máximo una vez por pedido
- Sin deleted_at en líneas: el borrado es físico vía cascade del pedido o replace en update
```

**Coherencia cabecera ↔ detalle**

- Tras persistir líneas, `sales_orders.total_amount` = `SUM(sales_order_items.line_total)` del pedido.
- En `update`, si se envían líneas anidadas, recomputar total en **transacción** (cabecera + reemplazo/sincronización de líneas).

### Validaciones

| Campo          | Reglas                                                                                                                                 |
| -------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| `company_id`   | Obligatorio; en rutas con sesión, debe coincidir con empresa seleccionada; inmutable en update salvo reglas explícitas de migración.    |
| `name`         | obligatorio, string, max razonable (p. ej. 255); **unique** respecto a `sales_orders` con `where(company_id, …)` (en update, ignore del registro actual) |
| `customer_id`  | obligatorio, existe en `sales_customers`; debe pertenecer a la misma `company_id` del pedido (validación custom o subquery)             |
| `address_id`   | obligatorio, existe en `sales_customer_addresses`; **`customer_id` de la dirección = `customer_id` del pedido**                         |
| `total_amount` | obligatorio, numérico, min según negocio (p. ej. ≥ 0), formato decimal coherente con la columna; debe igualar suma de líneas (validación custom post-cálculo) |

**Listado (`index` — query string)**

| Parámetro    | Reglas                                                                                                                                 |
| ------------ | -------------------------------------------------------------------------------------------------------------------------------------- |
| `date_from`  | opcional, fecha válida `Y-m-d`; si presente, filtrar `created_at` ≥ inicio de ese día (timezone app)                                  |
| `date_to`    | opcional, fecha válida `Y-m-d`; si presente, filtrar `created_at` ≤ fin de ese día; con `date_from`, `date_to` ≥ `date_from`           |
| `search`     | opcional, string; búsqueda por `name` (misma convención que otros listados del repo)                                                  |
| `sort`       | opcional, columna permitida (`name`, `created_at`, `total_amount`, …)                                                                 |
| `direction`  | opcional, `asc` \| `desc`                                                                                                              |
| `per_page`   | opcional, entero acotado                                                                                                               |

**Líneas (`items` o `lines` en payload anidado, si aplica update/alta)**

| Campo / regla    | Reglas                                                                                                                                 |
| ---------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| Arreglo de líneas | obligatorio en store completo; en update opcional según UI (si se envía, al menos un elemento)                                      |
| `product_id`     | obligatorio, existe en `stock_products`; `company_id` del producto = `company_id` del pedido; producto activo y no soft-deleted al crear/reemplazar |
| `quantity`       | obligatorio, entero, min: 1                                                                                                            |
| `unit_price`     | obligatorio, numérico, ≥ 0; si omitido en store, default = `Product::find(product_id)->price`                                          |
| `line_total`     | no confiar en cliente; calcular `quantity * unit_price` en Action                                                                      |
| Unicidad         | no duplicar `product_id` dentro del mismo request; BD refuerza `UNIQUE(order_id, product_id)`                                          |

### Rutas y acciones sugeridas

| Recurso / acción   | Notas                                                                 |
| ------------------ | --------------------------------------------------------------------- |
| `index`            | Listado paginado filtrado por `company_id` de sesión; query `search`, `sort`, `direction`, `per_page`, `date_from`, `date_to` (validados en Form Request o en Action). |
| `create` / `store` | Opcional en rutas si existe canal no-UI; policy `create`. **No** hay página Inertia de creación. |
| `edit` / `update`  | Edición; policy `update`; model binding solo pedidos de la empresa en sesión. |
| `destroy`          | **Eliminación física** (`$order->forceDelete()` o `delete()` sin SoftDeletes); policy `delete`. |

**Interfaces de usuario previstas (Inertia):**

- **Listar:** `resources/js/pages/sales/orders/index.tsx` — `TabledataProvider` con toolbar de filtros (`OrdersIndexFilters`: rango **Desde / Hasta**), menú **Columnas** para ocultar columnas, búsqueda y paginación; acciones según permisos (editar, eliminar si aplica; **sin** “Nuevo pedido” / sin ruta `create` en menú).
- **Editar:** `resources/js/pages/sales/orders/form.tsx` (o `edit.tsx`) — formulario solo para **actualizar** pedido existente; incluye sección **Detalle del pedido** (tabla de `OrderItem` con producto, cantidad, precio unitario, subtotal).

**Props Inertia sugeridas (edición / show implícito en edit)**

- `order`: cabecera (`name`, `customer_id`, `address_id`, `total_amount`, …).
- `order.items`: arreglo de líneas con al menos `id`, `product_id`, `quantity`, `unit_price`, `line_total`, y datos de presentación del producto (`name`, `code`, `sku` vía eager load o DTO).
- `products` (opcional, solo si UI permite editar líneas): listado acotado de productos activos de la empresa para el selector.

### Archivos involucrados

| Capa                           | Ruta                                                                                                                                                                            |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Rutas                          | `routes/sales.php`; grupo con `auth` + empresa seleccionada.                                                                                                                    |
| Controlador                    | `app/Http/Controllers/Sales/OrdersController.php`                                                                                                                               |
| Actions                        | `app/Actions/Sales/Orders/` (listar, actualizar cabecera ± sincronizar líneas, eliminar físico; store con líneas si se expone alta no-UI) — transacción DB al guardar pedido + items |
| Policy                         | `app/Policies/Sales/OrderPolicy.php` — `company_id` vs sesión en todas las abilities                                                                                           |
| Form Request                   | `UpdateOrderRequest` (y `StoreOrderRequest` si existe alta por API/proceso) — reglas anidadas `items.*.product_id`, `items.*.quantity`, `items.*.unit_price`                  |
| Modelo                         | `app/Models/Sales/Order.php` — **sin** `SoftDeletes`; `belongsTo` Company, Customer, CustomerAddress; `hasMany` OrderItem                                                       |
| Modelo líneas                  | `app/Models/Sales/OrderItem.php` — `belongsTo` Order, Product (`Stock\Product`); **sin** SoftDeletes                                                                           |
| Migración cabecera             | Creación de tabla `sales_orders` con FKs e índice único `(company_id, name)`                                                                                                    |
| Migración detalle              | Creación de tabla `sales_order_items` con FKs, `ON DELETE CASCADE` en `order_id`, único `(order_id, product_id)`                                                                |
| Seeder sistema/módulo/permisos | Extender seeder de sistemas/módulos/permisos con **Ventas** → **Pedidos** y permisos CRUD                                                                                       |
| Seeder datos                   | `database/seeders/Sales/OrderSeeder.php` (opcional): pedidos de prueba                                                                                                          |
| Vista listado                  | `resources/js/pages/sales/orders/index.tsx`, `config.ts`, `filters.tsx`, `types.ts` (filtros de módulo + `TabledataProvider`)                                                    |
| Action listado                 | `app/Actions/Sales/Orders/ListOrdersAction.php` — `company_id`, `search`, scope por `date_from`/`date_to` en `created_at`, ordenación y paginación                             |
| Form Request listado           | `IndexOrderRequest` o validación inline en controlador (fechas, sort, per_page)                                                                                                  |
| Vista formulario edición       | `resources/js/pages/sales/orders/form.tsx` + componente sugerido `order-items-table.tsx` (o sección inline)                                                                     |
| Tipos TS                       | `resources/js/pages/sales/orders/types.ts` — `OrderItemRow`, props de línea alineadas a Inertia                                                                                |
| Navegación                     | `resources/js/nav.ts`: grupo **Ventas**, hijo **Pedidos** con `permission: 'sales.orders.list'`                                                                               |

### Seeders

- Registrar **System** `sales` / “Ventas”, **Module** `sales.orders` / “Pedidos” y los cuatro **Permission** `sales.orders.{list,create,update,delete}`.
- Datos de prueba opcionales: `Order` asociados a `Customer` y `CustomerAddress` coherentes y a una `Company` de referencia en `Sales\\OrderSeeder`, cada uno con **≥1** `OrderItem` sobre `Product` de la misma empresa; `total_amount` = suma de `line_total`.

### Elementos de frontend

- [x] **Filtro por rango de fechas:** dos controles **Desde** y **Hasta** (tipo `date` o date picker del design system) en la toolbar del listado; al aplicar, enviar `date_from` / `date_to` en query string y reflejar en URL (Inertia visit) para enlazar/compartir; botón **Limpiar** restablece fechas y recarga sin ese filtro.
- [ ] Filtro por cliente (opcional; no requerido en v1)
- [x] Búsqueda sugerida por `name` dentro de la empresa actual
- [x] Ordenación (p. ej. `name`, `created_at`, `total_amount`)
- [x] Paginación
- [x] **Ocultar columnas:** menú **Columnas** del `TabledataProvider` (`columnVisibility` activo) con checkboxes por columna **ocultable**; **persistir preferencia** en `localStorage` con clave `sales-orders-{userId}-{companyId}` (misma convención que `stock-products-{userId}-{companyId}` en productos Stock).
- [x] **Orden de columnas sugerido en listado:** Nombre → Cliente → Dirección (resumen) → Importe total → Cant. líneas → Fecha creación → Acciones; columna **Nombre** con `hideable: false` (siempre visible).
- [x] Tabla de detalle en edición (producto, cantidad, precio unitario, subtotal)
- [ ] Edición interactiva de líneas en UI (añadir/eliminar filas; opcional v1)
- [x] Columna o badge en listado con **cantidad de líneas** o resumen breve

### Consideraciones técnicas

- **Autorización y multi-tenant:** toda query debe incluir `company_id` de `SelectedCompanySession::selectedCompanyId`; el policy debe denegar si el pedido o el cliente/dirección no pertenecen a la empresa en sesión.
- **Sin SoftDeletes:** el borrado es definitivo; confirmación en UI y posible auditoría externa si el producto la requiere más adelante.
- **Integridad cliente–dirección:** validar que `CustomerAddress::whereKey($address_id)->where('customer_id', $customer_id)` exista antes de guardar.
- **Integridad pedido–producto:** validar `Product::whereKey($product_id)->where('company_id', $order->company_id)->where('is_active', true)` al sincronizar líneas (omitir `is_active` solo para lectura de líneas históricas).
- **Unicidad `name`:** restricción `UNIQUE(company_id, name)` en BD más reglas de Form Request en update.
- **Filtro de fechas en listado:** scope en `Order` o en `ListOrdersAction`, p. ej. `scopeCreatedBetween(?string $from, ?string $to)` aplicando `whereDate` / rango inclusivo por día en timezone de la app; índice existente en `company_id` + orden por `created_at` suele bastar en v1.
- **Ocultar columnas:** reutilizar `@/components/custom/tabledata` (`TabledataProvider`, `hideable` por columna, `storageKey` + `perPageStorageKey` compartidos); no implementar UI paralela.
- **Eager loading:** en `edit`/`index`, `Order::with(['items.product', 'customer', 'address'])` (o atributos mínimos) para columnas de listado y detalle; evitar N+1.
- **Transacciones:** `DB::transaction` al crear/actualizar pedido con líneas; estrategia de sync en update: reemplazo completo del detalle (delete items del pedido + insert nuevos) o diff por `product_id` — documentar la elegida en la Action.
- **Snapshot de precio:** `unit_price` en `sales_order_items` desacopla el histórico del `stock_products.price` actual.
- Registrar `OrderPolicy` y rutas; ejecutar generación de Wayfinder tras definir rutas. **No** `OrderItemPolicy` público: autorizar siempre vía `OrderPolicy` sobre el pedido padre.
- Tests sugeridos: listado solo de la empresa en sesión; listado con `date_from`/`date_to` devuelve solo pedidos en rango; `date_to` anterior a `date_from` responde 422; update con `customer_id`/`address_id` incoherentes falla; duplicar `(company_id, name)` falla; `destroy` elimina la fila de pedido **y** sus líneas; otra empresa puede reutilizar el mismo `name`; store/update con `total_amount` distinto a suma de líneas falla; producto de otra empresa o inactivo en línea falla; duplicar `product_id` en el mismo pedido falla; pedido sin líneas falla en store.
