---
description: Mostrar en la landing pública los productos activos de Stock de la empresa PRODUCTOS BUENISIMOS SPA (hero con el primero, vitrina con el resto), sin medidas ni peso, imágenes solo placeholder único y sin página de detalle
type: negocio
date: 2026-05-13
status: draft
user: —
---

# Landing: productos de Stock (empresa PRODUCTOS BUENISIMOS SPA)

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

La página de inicio / landing pública debe reflejar el **catálogo real** almacenado en el módulo **Stock → Productos**, limitado a la empresa cuya razón social es **PRODUCTOS BUENISIMOS SPA**. El visitante ve un **producto destacado en el hero** y el **resto de productos en la sección de vitrina** (tarjetas), con **información básica** útil para presentación comercial. **No** se muestran dimensiones, volumen ni peso. Las **imágenes de producto** son, en esta fase, **únicamente un placeholder visual compartido** (no fotos por ítem). **No** existe ruta ni pantalla de ficha o detalle de producto en esta entrega: la landing es solo listado visual.

### Actores y roles

| Actor              | Acceso / comportamiento                                                                 |
| ------------------ | --------------------------------------------------------------------------------------- |
| Visitante (público) | Ve la landing con productos de la empresa indicada; no requiere sesión ni permisos de backoffice. |
| Usuario backoffice  | Sigue administrando productos en Stock; los cambios (altas, desactivaciones, etc.) se reflejan en la landing según reglas de visibilidad abajo. |

### Flujo principal

1. Un visitante abre la **ruta pública de la landing** (home o equivalente ya definido en el proyecto).
2. El sistema obtiene los productos elegibles de **PRODUCTOS BUENISIMOS SPA** y los ordena de forma estable.
3. El **primer producto** de esa lista se usa como contenido del **hero** (nombre, datos básicos acordados, sin medidas ni peso).
4. Los **demás productos** se muestran en la **sección de vitrina** (misma idea que la rejilla de tarjetas actual), también solo con datos básicos.
5. Si no hay productos elegibles, la landing debe degradar con un **estado vacío coherente** (mensaje breve y/o placeholders definidos en implementación), sin error 500.

### Reglas de negocio

- Solo productos de la empresa cuyo campo de razón social (o nombre oficial usado en `companies`) coincide con **PRODUCTOS BUENISIMOS SPA** (misma cadena que en `CompaniesSeeder` / datos de configuración).
- Solo productos **activos** (`is_active === true`) y **no eliminados lógicamente** (`deleted_at` nulo).
- **Orden de lista:** definir un criterio **único y estable** (recomendado: `created_at` ascendente) para que “el primero” del hero sea reproducible y alineado con el orden de carga de datos.
- **Hero:** exactamente **un** producto — el **primero** según el orden acordado.
- **Vitrina:** todos los **restantes** (desde el segundo en adelante). Si solo hay un producto, la vitrina puede estar vacía o mostrar solo mensaje de “más pronto”, según decisión de UX en implementación (documentar en código o comentario de PR).
- **Datos visibles en landing:** entre otros permitidos: identificador de fila (id), **nombre**, **código**, **SKU**, **precio** (formateado para Chile o convención del sitio), **stock mínimo** solo si el negocio lo considera “básico”; **prohibido** mostrar en esta página: **ancho, largo, alto, volumen, peso** (y cualquier derivado exclusivamente dimensional).
- **Imágenes:** por ahora **solo una imagen placeholder** (un único asset o URL fija) para **todos** los productos, en hero y en tarjetas; no se usan fotos reales por producto ni galerías hasta que exista campo de imagen en Stock y se amplíe el spec.
- **Fuera de alcance explícito:** página `/producto/:id`, modal de detalle ampliado, enlaces a ficha, ni exposición de medidas/peso aunque existan en base de datos.

### Datos que maneja el usuario

| Campo / concepto | Tipo visible        | Obligatorio en UI | Notas                                                                 |
| ---------------- | ------------------- | ----------------- | --------------------------------------------------------------------- |
| Nombre           | Texto               | Sí                | Titular del hero o de la tarjeta.                                     |
| Código / SKU     | Texto               | Según diseño      | Pueden mostrarse de forma compacta o omitirse si saturan; no son medidas. |
| Precio           | Moneda / etiqueta   | Sí si precio > 0  | Formato consistente con el resto del sitio.                          |
| Imagen           | Placeholder único   | Sí (visual)       | **Una sola** imagen placeholder compartida para hero y vitrina; el backend puede no enviar URL de imagen y la UI resuelve siempre ese asset fijo. |
| Subtítulo / copy | Texto               | No                | Si no hay campo en Stock, puede generarse desde código/SKU corto o texto fijo por sección. |

### Referencias visuales

Comportamiento visual esperado: reutilizar la composición existente de `resources/js/pages/landing/` (hero + `LandingFeaturedProducts` / tarjetas), sustituyendo el origen de datos estático por datos del backend según este spec.

---

## ESPECIFICACIÓN TÉCNICA

### Modelo de datos

Fuente: tabla `stock_products` (ya migrada). Para la landing solo interesan columnas **no dimensionales** para la respuesta pública; el backend **no** debe serializar hacia el frontend, en esta feature, los campos: `width`, `length`, `height`, `volume`, `weight`.

```
tabla: stock_products (existente)

- id: uuid
- company_id: uuid FK → configuration_companies
- name, code, sku: string
- minimum_stock: unsignedInteger (opcional en UI)
- price: decimal (12,2)
- is_active: boolean
- deleted_at: timestamp nullable (soft delete)
- created_at: timestamp (criterio de orden sugerido)
```

Resolución de empresa: localizar `company_id` donde `companies.name = 'PRODUCTOS BUENISIMOS SPA'` (o el atributo equivalente en `App\Models\Company` que represente la razón social usada en seeders).

### Validaciones

No aplica formulario de usuario en la landing. El servidor debe **defensivamente** filtrar por `company_id` resuelto y nunca aceptar `company_id` desde querystring del visitante para esta página pública (empresa fijada por negocio en código o constante de configuración).

### Archivos involucrados (orientativos)

| Capa            | Ruta                                                                 |
| --------------- | -------------------------------------------------------------------- |
| Ruta            | `routes/web.php` (ruta pública de landing ya existente)              |
| Controlador     | `app/Http/Controllers/LandingController.php`                         |
| Action (nuevo)  | `app/Actions/Stock/Products/` o `app/Actions/Landing/` — listar productos públicos por `company_id` con query acotada |
| Modelo          | `app/Models/Stock/Product.php`                                       |
| Modelo empresa  | `app/Models/Company.php`                                             |
| Vista landing   | `resources/js/pages/landing/index.tsx`, `landing-hero.tsx`, `landing-featured-products.tsx`, `landing-product-card.tsx` |
| Tipos           | `resources/js/pages/landing/types.ts` — ajustar props de página y tipo de producto showcase al contrato real |
| Datos estáticos | `resources/js/pages/landing/landing-showcase-data.ts` — dejar de usar como fuente de verdad o reducir a fallback |

La **ficha pública de detalle** (ruta `/productos/{uuid}`, todos los campos + placeholder) vive en **`ai/feat.landing-producto-detalle.spec.md`** e implementación asociada; el listado de landing enlaza a esa ruta.

### Seeders

Para pruebas locales y demos: asegurar que `ProductSeeder` (o flujo de seeds) cree productos **asociados** al `company_id` de **PRODUCTOS BUENISIMOS SPA** y que exista al menos un producto activo para validar hero + vitrina.

### Elementos de frontend

- [ ] Datos desde servidor (Inertia props), no solo estáticos
- [ ] Hero enlazado al primer producto de la lista ordenada
- [ ] Lista / tarjetas con el resto
- [ ] Imagen: **un solo** placeholder compartido (hero + vitrina), sin URLs por producto
- [ ] Estado vacío (sin productos elegibles)
- [ ] Filtros — No aplica en landing pública
- [ ] Búsqueda — No aplica
- [ ] Ordenamiento — Fijo en servidor (`created_at` asc u otro acordado)
- [ ] Paginación — No aplica (mostrar todos los elegibles; si el volumen crece, abrir issue futuro de paginación o “ver catálogo”)
- [ ] Ocultar columnas — No aplica

### Consideraciones técnicas

- **Caché (opcional):** la consulta puede cachearse unos minutos con clave por empresa para reducir carga; invalidación al actualizar productos es mejora opcional.
- **Seguridad:** la query pública no expone productos de otras empresas ni inactivos ni soft-deleted.
- **Contrato Inertia:** definir un DTO o array mínimo (p. ej. `heroProduct`, `showcaseProducts: []`) para que TypeScript coincida con lo que envía `LandingController`.
- **Imágenes:** implementación acotada a **un solo placeholder** (p. ej. constante en frontend o `asset()` a un SVG/PNG en `public/`), mismo recurso para hero y tarjetas; no serializar ni inventar `image_url` por producto en esta entrega.
- **Detalle:** enlaces desde hero y vitrina a la ficha pública (`landing.products.show`) según `feat.landing-producto-detalle.spec.md`.
