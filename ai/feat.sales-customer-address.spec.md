---
description: Dirección del cliente en entidad separada (Ventas), persistida y validada junto al formulario de Customer; sin permisos ni menú propios — reutiliza sales.customers.*
type: negocio
date: 2026-05-13
status: draft
user: —
---

# Dirección de cliente (entidad CustomerAddress, Ventas)

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Separar los datos de **dirección** del cliente en una **tabla y modelo propios** (`CustomerAddress`), manteniendo la experiencia de usuario **dentro del mismo flujo de clientes**: el usuario sigue entrando por **Ventas → Clientes** y edita o crea el cliente en la pantalla habitual; allí se completan **país**, **región/estado**, **dirección** y la vinculación al cliente.

No existe un módulo de menú ni permisos independientes para “direcciones”: **quien puede listar/crear/actualizar/eliminar clientes** según `sales.customers.*` es quien puede ver y guardar la dirección asociada, siempre en el **contexto del cliente** (misma empresa que el cliente vía `company_id` del padre).

### Actores y roles

| Actor                   | Acceso / comportamiento                                                                                                                                 |
| ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| root                    | Acceso total; no depende de permisos granulares.                                                                                                        |
| owner / usuario con rol | Misma matriz que **Clientes**: `sales.customers.list` para ver listado (y formulario al abrir cliente); `create` / `update` / `delete` para persistir cambios que incluyan dirección cuando aplique la acción sobre el cliente. |
| sin permiso             | Sin acceso al módulo Clientes; no aplica ninguna UI ni ruta específica de direcciones.                                                                  |

**Contexto de empresa:** igual que clientes — `EnsureCompanySelected` + `company_selected`; la dirección **no** introduce `company_id` propio: la pertenencia a empresa se infiere **solo** del `Customer` padre.

### Flujo principal

1. El usuario con permiso adecuado abre **Ventas → Clientes** (mismo `menu scope` que hoy: agrupación **Ventas**, hoja **Clientes** con `permission: 'sales.customers.list'`).
2. Desde el listado, crea o edita un **cliente** como hasta ahora.
3. En el **mismo formulario** de cliente aparecen campos de dirección: país, estado/región, texto de dirección (y cualquier ayuda visual alineada al diseño del proyecto).
4. Al guardar el cliente (alta o actualización), el backend **crea, actualiza o elimina** el registro de dirección asociado según reglas de negocio y validación — **sin** pantallas ni rutas públicas de “CRUD solo de dirección”.

### Reglas de negocio

- Toda dirección pertenece a **exactamente un** cliente (`customer_id` obligatorio, FK a `sales_customers`).
- **Cardinalidad:** un cliente tiene **como máximo un** bloque de dirección persistido (relación **1:0..1**): si el negocio requiere “sin dirección”, no hay fila en `sales_customer_addresses`; si hay datos de dirección, una sola fila por `customer_id` (índice único en `customer_id`).
- Los campos visibles al usuario para la dirección son: **nombre del país**, **nombre del estado/región** y **dirección** (texto libre o multilínea según convención UI del repo).
- **Permisos:** no se crean permisos `sales.customer_addresses.*` ni módulo de permisos nuevo en seeders. Toda autorización pasa por **Customer** (`CustomerPolicy` y mismas abilities que el CRUD de clientes).
- **Menú:** no se añade entrada de navegación para “Direcciones”; el alcance sigue siendo el ítem **Clientes** bajo **Ventas**.
- La dirección **no** debe poder asociarse a un cliente de **otra** empresa que la de sesión: validar que el `customer_id` resuelto pertenezca a `company_id` de sesión (misma regla que edición de cliente).

### Datos que maneja el usuario

| Campo            | Tipo visible | Obligatorio | Notas                                                                 |
| ---------------- | ------------ | ----------- | --------------------------------------------------------------------- |
| País             | Texto        | A definir   | Nombre del país (`country_name`); puede ser obligatorio u opcional según producto. |
| Estado / región  | Texto        | A definir   | Nombre del estado (`state_name`).                                     |
| Dirección        | Texto        | A definir   | Líneas de dirección (`address`); longitud máxima alineada a validación técnica. |

*(La obligatoriedad concreta de cada campo puede fijarse en implementación: todo opcional salvo que el producto exija dirección completa en alta.)*

### Referencias visuales

<!-- No aplica: la UI es sección dentro del formulario de cliente. -->

---

## ESPECIFICACIÓN TÉCNICA

### Sistema, módulo, permisos y menú

| Concepto                     | Valor                                                                                                                                                                                                 |
| ---------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Sistema (`slug`)             | `sales` (Ventas)                                                                                                                                                                                      |
| Módulo funcional             | **Clientes** (`sales.customers`) — la dirección es **sub-recurso de dominio** del mismo módulo.                                                                                                        |
| Permisos                     | **Reutilizar** `sales.customers.list`, `sales.customers.create`, `sales.customers.update`, `sales.customers.delete`. **No** registrar permisos adicionales en `PermissionsSeeder` para direcciones. |
| Policy                       | **No** `CustomerAddressPolicy` para abilities públicas: autorizar vía **`CustomerPolicy`** sobre el `Customer` padre (create/update/delete del cliente implica manipular dirección anidada cuando corresponda). |
| **Alcance del menú**         | **Idéntico a Clientes:** grupo **Ventas**, hoja **Clientes** con `permission: 'sales.customers.list'`. Sin nuevas claves en `resources/js/nav.ts` para dirección.                                    |

### Contexto de empresa (sesión)

| Elemento | Uso en CustomerAddress                                                                 |
| -------- | -------------------------------------------------------------------------------------- |
| Sesión   | Misma que `Customer`: no se expone `company_id` en el modelo de dirección.             |
| Consultas | Siempre join o subquery vía `customer` con `where('company_id', $companyId)` de sesión. |
| Alta/actualización | El `customer_id` debe corresponder a un `Customer` activo (no eliminado en soft delete) de la empresa en sesión. |

### Relaciones

| Modelo / entidad   | Relación   | Notas |
| ------------------ | ---------- | ----- |
| `CustomerAddress`  | `belongsTo` | `Customer` vía `customer_id` (FK obligatoria, `ON DELETE` acorde a política del proyecto — p. ej. `cascade` físico o limpieza en Action al eliminar cliente). |
| `Customer`         | `hasOne`    | `CustomerAddress` (opcional: `customer_id` único en tabla hija). |

### Modelo de datos

```
tabla: sales_customer_addresses

- id: uuid, PK
- customer_id: uuid, NOT NULL, FK → sales_customers(id), UNIQUE (como máximo una dirección por cliente)
- country_name: string, nullable u NOT NULL según reglas de negocio finales
- state_name: string, nullable u NOT NULL según reglas de negocio finales
- address: text o string largo, nullable u NOT NULL según reglas de negocio finales
- created_at / updated_at: timestamps

Índices:
- UNIQUE (customer_id)
- INDEX (customer_id) — redundante con UNIQUE si el motor lo cubre; mantener UNIQUE como restricción principal.

Notas:
- **SoftDeletes:** no requerido en v1 para la dirección salvo que el producto exija baja lógica independiente; al eliminar el cliente en baja lógica, definir si se borra la fila de dirección o queda huérfana (preferible **eliminar en misma transacción** que el soft delete del padre o FK con cascade según estrategia elegida).
```

### Validaciones

| Campo          | Reglas                                                                                                                                    |
| -------------- | ----------------------------------------------------------------------------------------------------------------------------------------- |
| `customer_id`  | Obligatorio en persistencia; debe existir `Customer` con ese id, `company_id` = empresa de sesión, y cliente **no** soft-deleted para edición normal. |
| `country_name` | string, max razonable (p. ej. 120) si aplica.                                                                                              |
| `state_name`   | string, max razonable (p. ej. 120) si aplica.                                                                                            |
| `address`      | string o text, max razonable (p. ej. 500–2000) según columna elegida.                                                                     |

Validación **anidada** en los mismos `StoreCustomerRequest` / `UpdateCustomerRequest` (o request dedicado incluido solo desde el flujo del cliente), con reglas bajo clave tipo `address.country_name`, etc., según convención del proyecto.

### Rutas y acciones sugeridas

| Recurso / acción | Notas                                                                                                                                        |
| ---------------- | -------------------------------------------------------------------------------------------------------------------------------------------- |
| Rutas dedicadas  | **No** se exponen rutas REST públicas tipo `/sales/customer-addresses` para CRUD independiente.                                            |
| Persistencia     | **Actions** de creación/actualización (y opcionalmente eliminación) de `Customer` orquestan `CustomerAddress` en la misma unidad de trabajo (transacción DB). |

**Interfaces de usuario (Inertia):**

- **No** hay `resources/js/pages/sales/customer-addresses/*`.
- Los campos de dirección viven en **`resources/js/pages/sales/customers/form.tsx`** (o componente hijo importado desde ahí), tipados en el payload compartido con el formulario de cliente.

### Archivos involucrados

| Capa            | Ruta / nota                                                                                                                                                    |
| --------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Rutas           | Sin cambios de rutas **solo** por dirección; continúa `routes/sales.php` con el grupo de **Customers**.                                                      |
| Controlador     | `CustomersController` — props de Inertia y/o validación que incluyan el bloque `address`.                                                                      |
| Actions         | `app/Actions/Sales/Customers/` — crear/actualizar cliente y sincronizar `CustomerAddress` (create/update/delete del hijo según payload).                       |
| Policy          | `app/Policies/Sales/CustomerPolicy.php` únicamente (sin policy separada para dirección en abilities HTTP).                                                     |
| Form Request    | Extender `StoreCustomerRequest` / `UpdateCustomerRequest` con reglas anidadas o array `address.*`.                                                             |
| Modelo          | `app/Models/Sales/CustomerAddress.php`; en `Customer`, relación `hasOne(CustomerAddress::class)`.                                                              |
| Migración       | `database/migrations/..._create_sales_customer_addresses_table.php`                                                                                          |
| Factory / Seeder| Opcional: `CustomerAddressFactory`, extensión de `CustomerSeeder` para clientes con dirección de ejemplo.                                                     |
| Vistas          | Solo `resources/js/pages/sales/customers/form.tsx` (+ hooks/types compartidos del módulo `customers`).                                                         |
| Navegación      | Sin cambios: `resources/js/nav.ts` mantiene solo **Clientes** bajo Ventas.                                                                                     |

### Seeders

- **No** añadir módulo ni permisos nuevos para direcciones.
- Opcional: en `CustomerSeeder` (o factory), crear algunas direcciones ligadas a clientes de prueba.

### Elementos de frontend

- [ ] Filtros (no aplica a entidad sin listado propio)
- [ ] Búsqueda independiente (no aplica)
- [ ] Ordenamiento (no aplica)
- [ ] Paginación (no aplica)
- [ ] Ocultar columnas (no aplica)

**En el formulario de cliente:** sección de dirección con los tres campos; envío junto al resto del DTO del cliente.

### Consideraciones técnicas

- **Transacciones:** guardar cliente + dirección en una sola transacción para evitar estados inconsistentes.
- **Eager load:** en `edit` de cliente, `with('address')` para hidratar el formulario sin N+1.
- **Wayfinder:** sin nuevas rutas de recurso “address”; regenerar Wayfinder solo si cambian firmas del controlador de clientes.
- **Tests:** en `CustomersCrudTest` (o similar), casos que comprueben creación/actualización de cliente con y sin bloque `address`; que no se pueda colgar una dirección de un `customer_id` de otra empresa; unicidad 1:1 por `customer_id`.
