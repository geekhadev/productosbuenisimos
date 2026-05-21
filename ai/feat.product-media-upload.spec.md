---
description: Carga de imágenes (hasta 5, con optimización y redimensionado) y video (sin edición) por producto del módulo Stock, con lógica de procesamiento de medios desacoplada en utilidades reutilizables
type: negocio
date: 2026-05-19
status: draft
user: Irwing Naranjo
---

# Carga de imágenes y video de producto (Stock)

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Permitir que cada producto del catálogo tenga **hasta 5 imágenes** y **un video opcional**. Las imágenes se optimizan y redimensionan automáticamente al momento de la carga para no penalizar el rendimiento ni el almacenamiento; el video se almacena tal cual sin ninguna transformación. El usuario puede subir, ordenar y eliminar imágenes individualmente, y reemplazar o eliminar el video. Todo esto ocurre dentro del flujo de edición del producto existente.

### Actores y roles

| Actor                   | Acceso / comportamiento                                                                                       |
| ----------------------- | ------------------------------------------------------------------------------------------------------------- |
| root                    | Acceso total; puede gestionar medios de cualquier empresa.                                                    |
| owner / usuario con rol | Requiere permiso `stock.products.update` para cargar, reordenar o eliminar medios de un producto existente.  |
| sin permiso             | No puede acceder al formulario de edición ni invocar rutas de medios directamente.                            |

**Contexto de empresa:** la gestión de medios aplica solo a productos de la empresa seleccionada en sesión, igual que el resto del CRUD de productos.

### Flujo principal

1. El usuario con permiso **Actualizar** abre la edición de un producto activo.
2. Ve una sección de medios separada del formulario de datos del producto, con una zona de carga para imágenes y otra para el video.
3. **Imágenes:** arrastra o selecciona hasta 5 archivos de imagen. El sistema muestra previsualizaciones en miniatura. Las imágenes se pueden reordenar arrastrando (el orden define cómo se muestran en el catálogo). Cada imagen tiene un botón para eliminarla individualmente.
4. **Video:** sube un único archivo de video. Si el producto ya tiene video, se muestra con opción de reemplazar o eliminar; no existe edición del video.
5. Cada operación (cargar, reordenar, eliminar) se envía al servidor de forma independiente (sin esperar a guardar el formulario de datos). El estado de la sección de medios se actualiza en tiempo real.
6. Al entrar en modo lectura (producto inactivo, sin permiso de edición), los medios son visibles pero no se pueden modificar.

### Reglas de negocio

- Un producto puede tener **0 a 5 imágenes**. Subir una imagen cuando ya hay 5 es rechazado con mensaje claro.
- Las imágenes se **optimizan** automáticamente si su tamaño en bytes supera un umbral configurable (p. ej. 2 MB) y se **redimensionan** a un máximo de dimensiones configurable (p. ej. 2 048 px en el lado mayor) **sin perder la relación de aspecto ni degradar la calidad por encima de lo necesario**.
- Se aceptan los formatos de imagen `image/jpeg`, `image/png`, `image/webp`.
- El **order** de las imágenes es persistido en base de datos; el primero en la lista es considerado la imagen principal del producto.
- Un producto puede tener **0 o 1 video**. Si ya existe video, la nueva carga lo reemplaza (el archivo anterior se elimina del almacenamiento).
- Se aceptan los formatos de video `video/mp4`, `video/webm`, `video/quicktime` (`.mov`).
- Los archivos de medios se eliminan del disco cuando se borra el medio en base de datos; al hacer soft delete del producto, los archivos **no** se eliminan del disco (quedan huérfanos visibles si el producto es restaurado). En una eliminación física (si la hubiera), sí deben eliminarse.
- Los medios pertenecen al producto y, por transitividad, a la empresa; no se pueden servir medios cruzando empresas.
- Solo se pueden gestionar medios de productos **activos** (`is_active === true`), igual que la edición de datos.

### Datos que maneja el usuario

| Campo           | Tipo visible                    | Obligatorio | Notas                                                                                         |
| --------------- | ------------------------------- | ----------- | --------------------------------------------------------------------------------------------- |
| Imágenes        | Zona de carga (multi-archivo)   | No          | Hasta 5 archivos. JPG, PNG, WebP. Cada archivo max. 10 MB antes de optimizar.                |
| Orden imágenes  | Arrastrar y soltar (drag & drop)| No          | El índice 0 es la imagen principal.                                                           |
| Video           | Zona de carga (un solo archivo) | No          | Un archivo. MP4, WebM, MOV. Max. 200 MB.                                                      |

### Referencias visuales

<!-- No aplica en esta versión del spec. -->

---

## ESPECIFICACIÓN TÉCNICA

### Sistema, módulo, permisos y menú

| Concepto         | Valor                                                                                                       |
| ---------------- | ----------------------------------------------------------------------------------------------------------- |
| Sistema          | Stock (`stock`)                                                                                             |
| Módulo           | Productos (`stock.products`)                                                                                |
| Permisos usados  | `stock.products.update` para cargar/reordenar/eliminar medios. No requiere permisos nuevos.                 |
| Menú             | Sin cambios. El acceso a medios ocurre dentro de la vista de edición del producto.                          |

### Contexto de empresa (sesión)

Igual que el CRUD base de productos: `SelectedCompanySession::selectedCompanyId($request)` para verificar en policy y en resolución de ruta que el producto pertenece a la empresa en sesión.

### Modelo de datos

```
tabla: stock_product_media

- id:           uuid, PK
- product_id:   uuid, NOT NULL, FK → stock_products(id), index; CASCADE DELETE físico
                (cuando se elimina físicamente el producto, los registros de media caen también)
- type:         enum('image', 'video'), NOT NULL
- path:         string, NOT NULL           — ruta relativa al disco 'public'
- sort_order:   unsigned tinyint, NOT NULL, default 0
                (para imágenes: orden de visualización; para video siempre 0)
- disk:         string, NOT NULL, default 'public'
- mime_type:    string, NOT NULL
- size:         unsigned bigint, NOT NULL  — bytes del archivo almacenado
- original_name: string, NOT NULL         — nombre original del archivo subido
- created_at / updated_at: timestamps

Índices:
- INDEX (product_id)
- INDEX (product_id, type)
```

**Nota:** no se agregan columnas al modelo `Product` existente; la relación se resuelve vía `HasMany` al modelo nuevo `ProductMedia`.

### Utilidades de procesamiento de medios

La lógica de procesamiento **no debe vivir en los Actions**. Se extraen en clases de soporte bajo `app/Support/Media/`:

#### `app/Support/Media/ImageOptimizer.php`
Clase invocable o con método estático `optimize(UploadedFile $file, int $maxBytes): string` (devuelve ruta del archivo temporal procesado). Responsabilidad exclusiva: reducir el peso del archivo si supera `$maxBytes` ajustando la calidad del codec, sin cambiar dimensiones.

#### `app/Support/Media/ImageResizer.php`
Clase invocable o con método estático `resize(string $tempPath, int $maxSide): string`. Responsabilidad exclusiva: redimensionar la imagen a que ningún lado supere `$maxSide` píxeles, **preservando la relación de aspecto**, sin reencuadrar ni recortar.

#### `app/Support/Media/MediaUploader.php`
Clase con método `upload(string $tempPath, string $directory, string $disk): string`. Responsabilidad exclusiva: mover el archivo (ya procesado) al disco de almacenamiento y devolver la ruta relativa persistida en base de datos.

El pipeline en los Actions sigue el orden: **validar → optimizar (si imagen) → redimensionar (si imagen) → subir → persistir registro en BD**.

**Librería sugerida para procesamiento de imagen:** `intervention/image` v3 (compatible con Laravel 11+). Gestiona tanto optimización de calidad como redimensionado. Agregar como dependencia de Composer.

### Validaciones

| Campo          | Reglas                                                                                                                 |
| -------------- | ---------------------------------------------------------------------------------------------------------------------- |
| `images`       | `array`, max 5 elementos; si el producto ya tiene imágenes, la suma total (existentes + nuevas) no debe superar 5.    |
| `images.*`     | `file`, `image`, `mimes:jpg,jpeg,png,webp`, `max:10240` (10 MB; se valida el archivo original antes de optimizar).   |
| `video`        | `file`, `mimetypes:video/mp4,video/webm,video/quicktime`, `max:204800` (200 MB).                                       |
| `order`        | `array`; cada elemento es un `uuid` válido que corresponde a un `ProductMedia` de tipo `image` del producto actual.   |
| `media_id`     | En eliminación: `uuid`, debe existir en `stock_product_media` y pertenecer al producto (empresa verificada por policy). |

### Rutas y acciones sugeridas

Las rutas de medios son rutas **anidadas** al producto, dentro del mismo grupo protegido por `auth` + `EnsureCompanySelected`.

| Método   | Ruta                                                     | Acción / Controlador                        | Policy ability |
| -------- | -------------------------------------------------------- | ------------------------------------------- | -------------- |
| `POST`   | `/stock/products/{product}/images`                       | `ProductImagesController@store`             | `update`       |
| `DELETE` | `/stock/products/{product}/images/{media}`               | `ProductImagesController@destroy`           | `update`       |
| `PUT`    | `/stock/products/{product}/images/reorder`               | `ProductImagesController@reorder`           | `update`       |
| `POST`   | `/stock/products/{product}/video`                        | `ProductVideoController@store`              | `update`       |
| `DELETE` | `/stock/products/{product}/video`                        | `ProductVideoController@destroy`            | `update`       |

Todas las rutas validan que el producto esté **activo** y pertenezca a la empresa en sesión (misma policy que edición de datos).

### Archivos involucrados

| Capa              | Ruta                                                                                  |
| ----------------- | ------------------------------------------------------------------------------------- |
| Rutas             | `routes/stock.php` — rutas anidadas bajo `products/{product}/images` y `.../video`   |
| Controladores     | `app/Http/Controllers/Stock/ProductImagesController.php`                              |
|                   | `app/Http/Controllers/Stock/ProductVideoController.php`                               |
| Form Requests     | `app/Http/Requests/Stock/UploadProductImagesRequest.php`                              |
|                   | `app/Http/Requests/Stock/ReorderProductImagesRequest.php`                             |
|                   | `app/Http/Requests/Stock/UploadProductVideoRequest.php`                               |
| Actions           | `app/Actions/Stock/Products/UploadProductImagesAction.php`                            |
|                   | `app/Actions/Stock/Products/DeleteProductMediaAction.php`                             |
|                   | `app/Actions/Stock/Products/ReorderProductImagesAction.php`                           |
|                   | `app/Actions/Stock/Products/UploadProductVideoAction.php`                             |
| Utilidades        | `app/Support/Media/ImageOptimizer.php`                                                |
|                   | `app/Support/Media/ImageResizer.php`                                                  |
|                   | `app/Support/Media/MediaUploader.php`                                                 |
| Modelo            | `app/Models/Stock/ProductMedia.php` — `belongsTo(Product)`, `HasUuids`, `HasFactory` |
| Relación en Model | `app/Models/Stock/Product.php` — agregar `hasMany(ProductMedia::class)`               |
| Migración         | `database/migrations/YYYY_MM_DD_create_stock_product_media_table.php`                 |
| Factory           | `database/factories/Stock/ProductMediaFactory.php`                                    |
| Vista form        | `resources/js/pages/stock/products/form.tsx` — agregar sección de medios              |
| Componente        | `resources/js/components/custom/product-media-uploader/index.tsx` — zona de carga de imágenes con previsualizaciones y drag & drop |
|                   | `resources/js/components/custom/product-video-uploader/index.tsx` — zona de carga de video con previsualización |

### Seeders

No se requieren datos semilla nuevos específicos para esta feature. La factory `ProductMediaFactory` debe poder generar registros con `path` ficticios válidos para usarse en tests.

### Elementos de frontend

- [x] **Sección de medios** en el formulario de edición del producto, visualmente separada de los campos de datos.
- [x] **Cargador de imágenes:** zona de drop o selector de archivo múltiple; muestra previsualizaciones en miniatura tras la selección; indicador de progreso por archivo; el contador `X / 5` indica cuántas se han subido.
- [x] **Reordenar imágenes:** drag & drop sobre las miniaturas; al soltar, se envía `PUT .../images/reorder` con el nuevo orden.
- [x] **Eliminar imagen individual:** botón sobre cada miniatura; confirmación ligera (tooltip o popover, no modal completo).
- [x] **Cargador de video:** zona de drop o selector de archivo único; si ya hay video, muestra el elemento `<video>` con controles y botones "Reemplazar" / "Eliminar".
- [x] **Estado de solo lectura:** si el producto es inactivo o el usuario no tiene permiso, la sección muestra los medios pero sin controles de edición.
- [x] **Manejo de errores:** mensajes inline para tipo/tamaño no admitidos, límite de 5 imágenes superado, fallo de red.

### Consideraciones técnicas

- **Separación de responsabilidades:** los Actions orquestan el flujo (llamar utils en orden, persistir en BD, devolver resultado). Ninguna lógica de procesamiento de imagen/video reside en el Action ni en el Controller.
- **Configuración de umbrales:** los valores `MAX_IMAGE_BYTES`, `MAX_IMAGE_SIDE_PX` deben ser constantes o valores leídos de `config/media.php` (nuevo archivo de configuración), no literales hardcodeados dentro de las utilidades.
- **Disco de almacenamiento:** usar el disco `public` de Laravel. Estructura de directorios sugerida: `products/{product_id}/images/{uuid}.webp` y `products/{product_id}/videos/{uuid}.mp4`. Convertir imágenes a WebP al guardar para uniformizar el formato de salida.
- **Limpieza de archivos al eliminar:** `DeleteProductMediaAction` debe llamar a `Storage::disk($media->disk)->delete($media->path)` **antes** de eliminar el registro en BD. Si el archivo no existe en disco, registrar el incidente pero continuar con el borrado del registro.
- **Reemplazo de video:** `UploadProductVideoAction` debe eliminar el archivo y el registro anterior antes de crear el nuevo. Usar una transacción de BD para no dejar registros huérfanos si falla la subida.
- **Transacciones:** toda acción que combina escritura en disco + escritura en BD debe hacerse en orden: primero disco (reversible), luego BD (si falla disco, no llegar a BD).
- **Seguridad:** validar el `mime_type` real del archivo (no solo la extensión) dentro del Form Request con `mimes` / `mimetypes`. El path generado debe usar un UUID, nunca el nombre original del archivo, para evitar traversal y colisiones.
- **Tests:** ver sección siguiente.

### Pruebas

| Escenario                                                                    | Tipo        |
| ---------------------------------------------------------------------------- | ----------- |
| Subir imagen válida → se crea registro en BD y archivo existe en disco       | Feature     |
| Subir imagen que supera umbral → archivo almacenado pesa menos               | Feature     |
| Subir imagen grande → dimensiones del archivo resultante ≤ `MAX_IMAGE_SIDE_PX` en el lado mayor | Feature |
| Subir la 6.ª imagen → 422 con mensaje de límite superado                     | Feature     |
| Reordenar imágenes → `sort_order` actualizado en BD en el orden enviado      | Feature     |
| Eliminar imagen → registro eliminado de BD y archivo eliminado de disco       | Feature     |
| Subir video cuando ya existe otro → video anterior eliminado de disco y BD    | Feature     |
| Subir archivo con extensión válida pero mime real inválido → 422              | Feature     |
| Usuario sin permiso `update` intenta subir → 403                             | Feature     |
| Producto inactivo → subir imagen → 403                                        | Feature     |
| `ImageResizer` recibe imagen de 3 000 × 2 000 px → resultado 2 048 × 1 365 px (relación conservada) | Unit |
| `ImageOptimizer` recibe archivo de 4 MB → resultado < 2 MB                   | Unit        |
| `MediaUploader` sube archivo y devuelve path relativo válido                  | Unit        |
