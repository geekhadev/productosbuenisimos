---
description: [Descripción corta de la funcionalidad]
type: [negocio | técnico | refactor | bug]
date: YYYY-MM-DD
status: draft
# status: draft | implemented
user: [Nombre del autor]
---

<!--
  INSTRUCCIONES DE USO
  ────────────────────
  1. Copia este archivo con el nombre: <tipo>.<slug-de-la-funcionalidad>.spec.md
     Ejemplo: feat.crea-modulo-clientes.spec.md
  2. Cambia `status` a `implemented` cuando el feature esté en producción.
  3. Elimina estas instrucciones antes de guardar.
-->

# [Título de la funcionalidad]

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

[Explica el problema de negocio que resuelve esta funcionalidad. Sin tecnicismos.
Responde: ¿qué puede hacer el usuario que antes no podía?]

### Actores y roles

| Actor | Acceso / comportamiento |
|-------|------------------------|
| root  | [descripción]           |
| owner | [descripción]           |
| ...   | ...                     |

### Flujo principal

1. [Paso 1 desde la perspectiva del usuario]
2. [Paso 2]
3. [Paso 3]

### Reglas de negocio

- [Regla 1: condición → consecuencia]
- [Regla 2]

### Datos que maneja el usuario

| Campo           | Tipo visible | Obligatorio | Notas                    |
|-----------------|--------------|-------------|--------------------------|
| [nombre campo]  | Texto        | Sí          | [descripción o ejemplo]  |

### Referencias visuales

<!-- Agrega imágenes o wireframes si existen -->
<!-- ![Descripción](./screenshot.png) -->

---

## ESPECIFICACIÓN TÉCNICA

### Modelo de datos

```
tabla: [nombre_tabla]

- [columna]: [tipo] [restricciones]
- [columna]: [tipo] [restricciones]
```

### Validaciones

| Campo   | Reglas                                  |
|---------|-----------------------------------------|
| [campo] | obligatorio, único, string, max:255, …  |

### Archivos involucrados

| Capa          | Ruta                                                       |
|---------------|------------------------------------------------------------|
| Ruta          | `routes/[archivo].php`                                     |
| Controlador   | `app/Http/Controllers/[Dominio]/[Módulo]Controller.php`   |
| Actions       | `app/Actions/[Dominio]/[Módulo]/`                          |
| Policy        | `app/Policies/[Dominio]/[Módulo]Policy.php`                |
| Form Request  | `app/Http/Requests/[Dominio]/[Módulo]Request.php`          |
| Modelo        | `app/Models/[Modelo].php`                                  |
| Migración     | `database/migrations/YYYY_MM_DD_000000_[descripcion].php`  |
| Seeder        | `database/seeders/[Dominio]/[Módulo]Seeder.php`            |
| Vista listado | `resources/js/pages/[dominio]/[modulo]/index.tsx`          |
| Vista form    | `resources/js/pages/[dominio]/[modulo]/form.tsx`           |

### Seeders

<!-- Describe los datos semilla necesarios o escribe "No aplica." -->

### Elementos de frontend

- [ ] Filtros
- [ ] Búsqueda
- [ ] Ordenamiento
- [ ] Paginación
- [ ] Ocultar columnas

### Consideraciones técnicas

- [Consideración 1: patrón a seguir, middleware, tenant scope, etc.]
- [Consideración 2]
