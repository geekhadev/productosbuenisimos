---
description: Ficha pública de detalle de producto al abrirlo desde la landing (PRODUCTOS BUENISIMOS SPA): todos los datos del ítem e imagen placeholder; sin compra ni checkout
type: negocio
date: 2026-05-13
status: draft
user: —
---

# Landing: detalle de producto (desde vitrina / hero)

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

El visitante de la **landing pública** debe poder **abrir la ficha de un producto** desde el **hero** o desde una **tarjeta de la vitrina** y ver **toda la información del producto** tal como está almacenada en Stock (incluidas dimensiones, volumen, peso, stock mínimo y precio), con **una imagen placeholder** (mismo criterio visual que en listado: asset único compartido, no fotos por producto en esta fase).

**Fuera de alcance explícito e intocable en esta feature:** cualquier flujo de **compra**, **carrito**, **cotización**, **cantidad a comprar**, **botón “Comprar” / “Añadir al carrito”**, **pasarela de pago**, **checkout** o **registro obligatorio para comprar**. La pantalla es **solo informativa**.

### Actores y roles

| Actor               | Acceso / comportamiento                                                                 |
| ------------------- | --------------------------------------------------------------------------------------- |
| Visitante (público) | Abre detalle solo de productos **elegibles** de la empresa de catálogo público; no requiere sesión. |
| Usuario backoffice  | Sigue gestionando productos en Stock; altas, desactivaciones y bajas lógicas afectan qué productos son visibles en detalle público. |

### Flujo principal

1. El visitante está en la **landing** y ve el **hero** y/o la **vitrina** con productos de **PRODUCTOS BUENISIMOS SPA** (según spec de landing).
2. Hace clic en un **área enlazada** del hero o de una tarjeta (nombre, tarjeta entera o botón tipo “Ver detalle” — decisión de UX en implementación).
3. El navegador navega a la **ruta pública de detalle** del producto (identificador estable, p. ej. UUID).
4. El sistema muestra la **ficha** con **todos los campos de detalle** acordados abajo y la **imagen placeholder**.
5. Si el producto **no existe**, **no pertenece** a esa empresa de catálogo, está **inactivo** o está **eliminado lógicamente**, el visitante recibe **404** (sin filtrar información de otras empresas).

### Reglas de negocio

- Solo productos de la empresa cuya identificación coincide con la usada en la landing pública (**PRODUCTOS BUENISIMOS SPA** — misma resolución que en `feat.landing-productos-stock.spec.md`).
- Solo productos **activos** (`is_active === true`) y **sin soft delete** (`deleted_at` nulo).
- **Detalle completo en UI:** mostrar al menos: **nombre**, **código**, **SKU**, **precio** (formato monetario acorde al sitio), **stock mínimo**, **ancho**, **largo**, **alto**, **volumen**, **peso** (unidades y etiquetas claras para el visitante; si un valor es 0, mostrar “0” o “—” según convención única del proyecto).
- **Imagen:** **única** imagen **placeholder** compartida (no URL por producto ni galería hasta existir campo de imagen en dominio).
- **Navegación:** debe existir forma clara de **volver a la landing** (enlace “Volver”, breadcrumb mínimo o logo — a definir en UI sin ampliar alcance de compra).
- **Prohibido** en esta entrega: CTA de compra, formularios de pedido, enlaces a checkout, integración con pasarelas, lógica de inventario reservable para venta.

### Datos que maneja el usuario

El visitante **no edita** datos; solo **lee**. Referencia de superficie visible:

| Campo / concepto | Tipo visible      | Obligatorio en ficha | Notas                                      |
| ---------------- | ----------------- | -------------------- | ------------------------------------------ |
| Nombre           | Texto             | Sí                   | Titular principal.                         |
| Código           | Texto             | Sí                   |                                            |
| SKU              | Texto             | Sí                   |                                            |
| Precio           | Moneda / decimal  | Sí                   | Formato Chile o convención del sitio.      |
| Stock mínimo     | Número entero     | Sí                   | Etiqueta legible (“Stock mínimo”, etc.).    |
| Ancho / Largo / Alto | Decimal       | Sí                   | Con unidad (p. ej. cm) si el producto lo define el diseño. |
| Volumen          | Decimal           | Sí                   | Unidad coherente (p. ej. cm³ o m³).       |
| Peso             | Decimal           | Sí                   | Unidad coherente (p. ej. kg o g).          |
| Imagen           | Placeholder único | Sí (visual)          | Mismo asset que en landing o equivalente.  |
| Metadatos internos | No mostrar      | No                   | No mostrar `company_id` ni flags internos irrelevantes al visitante. |

### Referencias visuales

Composición sugerida: layout de **ficha** (columna imagen placeholder + columna datos o stack móvil) alineado con tipografía y espaciado de `resources/js/pages/landing/`. Wireframes opcionales en PR.

---

## ESPECIFICACIÓN TÉCNICA

### Modelo de datos

Fuente: tabla `stock_products` (existente). **No** se requiere migración para esta feature si solo se expone lectura pública.

```
tabla: stock_products

- id: uuid (clave en URL pública)
- company_id: uuid FK
- name, code, sku: string
- width, length, height, volume, weight: decimal(12,3)
- minimum_stock: unsignedInteger
- price: decimal(12,2)
- is_active: boolean
- deleted_at: nullable (soft delete)
- created_at, updated_at: timestamps
```

**Resolución de empresa:** mismo criterio que landing — `company_id` de **PRODUCTOS BUENISIMOS SPA** vía `Company` / seeders; **no** aceptar `company_id` desde querystring del visitante.

### Validaciones

No hay formulario. El servidor valida implícitamente: producto encontrado por `id` + `company_id` fijado + `is_active` + `deleted_at` null; en caso contrario **404**.

### Archivos involucrados (orientativos)

| Capa            | Ruta                                                                 |
| --------------- | -------------------------------------------------------------------- |
| Ruta            | `routes/web.php` (ruta pública nueva, p. ej. `GET /productos/{product}` o slug acordado con el proyecto) |
| Controlador     | `app/Http/Controllers/LandingController.php` o controlador dedicado p. ej. `PublicProductController.php` |
| Action          | `app/Actions/Landing/ShowPublicProductAction.php` (o bajo `Stock/Products/` si el equipo prefiere agrupar por dominio) — consulta única acotada por empresa + uuid |
| Modelo          | `app/Models/Stock/Product.php` — posible **scope** o query en Action; **no** reutilizar `resolveRouteBinding` de sesión backoffice para esta ruta pública |
| Vista Inertia   | `resources/js/pages/landing/product-show.tsx` (nombre final acorde a convención del repo) |
| Tipos           | `resources/js/pages/landing/types.ts` o archivo de tipos colocalizado para el DTO de ficha pública |
| Landing listado | `resources/js/pages/landing/*.tsx` — enlazar hero y tarjetas a la nueva ruta con **Wayfinder** (`@/routes` / `@/actions` según convención del proyecto) |

### Seeders

Reutilizar datos de `ProductSeeder` / empresa **PRODUCTOS BUENISIMOS SPA** para probar ficha con valores distintos de cero en dimensiones y precio.

### Elementos de frontend

- [x] Ruta y página Inertia de detalle
- [x] Enlaces desde hero y tarjetas de vitrina hacia detalle
- [x] Listado de todos los campos de producto con formato legible
- [x] Imagen placeholder única
- [x] Estado 404 manejado (página de error del sitio o componente dedicado según stack Inertia)
- [ ] Filtros / búsqueda / ordenamiento / paginación — No aplica en ficha
- [ ] Ocultar columnas — No aplica

### Consideraciones técnicas

- **Autorización pública:** la query debe estar **hardcodeada o configurada** a la empresa de catálogo landing; nunca confiar en parámetros del cliente para el ámbito multi-tenant.
- **Seguridad:** no filtrar mensajes que revelen si un UUID existe en otra empresa; **404 uniforme** para “no accesible”.
- **Contrato Inertia:** un objeto `product` (o nombre equivalente) con todos los atributos visibles serializados; tipos TS alineados con el backend.
- **Wayfinder:** generar funciones tipadas para la nueva ruta y usarlos en hero y tarjetas en lugar de URLs literales.
- **Actualización del spec de landing:** al implementar, marcar en `feat.landing-productos-stock.spec.md` que la ficha pública **sí** entra en alcance o fusionar referencias cruzadas para evitar contradicción (“no enlace a detalle” → sustituir por enlace a esta ruta).
- **Compra:** no añadir rutas, componentes, ni props relacionadas con carrito/checkout; code review debe rechazar CTA de compra en esta PR.

### Pruebas

- Test de feature: visitante puede cargar detalle con producto válido; 404 con UUID inexistente, empresa distinta, `is_active = false` o soft-deleted.
- Opcional: test de que la respuesta incluye campos dimensionales esperados en el DTO público.
