---
description: Vista de conversaciones del chatbot por empresa; panel izquierdo con lista de conversaciones (lead o cliente, source, última actividad) y panel derecho con el hilo completo de mensajes; solo lectura
type: negocio
date: 2026-05-21
status: draft
user: Irwing Naranjo
---

# Vista de conversaciones del chatbot (Ventas)

---

## ESPECIFICACIÓN DE NEGOCIO

### Descripción

Permitir que el equipo de ventas **consulte** todas las conversaciones que los leads o clientes han tenido con el agente de chatbot. La vista es de **solo lectura**: no se edita ni se elimina nada. El objetivo es que el equipo pueda entender qué preguntó el contacto, qué respondió el agente y desde qué canal se originó la interacción.

### Actores y roles

| Actor                   | Acceso / comportamiento                                                                        |
| ----------------------- | ---------------------------------------------------------------------------------------------- |
| root                    | Acceso total; no depende de permisos granulares.                                               |
| owner / usuario con rol | Solo accede si tiene el permiso `sales.conversations.list` asignado a su rol.                  |
| sin permiso             | No ve la entrada de menú ni puede invocar las rutas del módulo.                                |

### Flujo principal

1. El usuario abre el menú bajo el grupo **Ventas** y entra a **Conversaciones**.
2. Ve el panel izquierdo con la lista de conversaciones de la empresa, ordenadas por la más reciente arriba.
3. Cada ítem del listado muestra:
   - Nombre del cliente (si el lead fue convertido y el cliente tiene `full_name`) o teléfono del lead.
   - Badge de tipo: **Cliente** (verde) si hay `customer_id`, **Lead** (amarillo) con el estado si aún es lead.
   - Ícono/badge del source donde se **inició** la conversación (web, WhatsApp, Facebook, Instagram).
   - Fecha/hora del último mensaje o de creación de la conversación.
   - Fragmento del último mensaje (recortado a ~60 caracteres).
4. Al hacer clic en un ítem, el panel derecho muestra el hilo completo de mensajes:
   - Los mensajes del usuario aparecen alineados a la derecha (burbuja oscura).
   - Los mensajes del agente aparecen alineados a la izquierda (burbuja clara).
   - Cada mensaje muestra su hora.
   - Si el `source` de un mensaje **difiere** del source inicial de la conversación, se muestra un badge de source junto al mensaje (indica que ese tramo llegó por otro canal).
5. El panel derecho muestra en el encabezado:
   - Nombre o teléfono del contacto.
   - Badge de tipo (cliente / lead + estado).
   - Badge del source principal de la conversación.
   - Si el lead está vinculado a un cliente, un enlace al perfil del cliente.

### Reglas de negocio

- Las conversaciones están acotadas a la **empresa en sesión** (`company_id`).
- Una conversación se vincula a un lead buscando en `sales_leads` por `phone` + `company_id`. Puede haber más de un lead con el mismo teléfono; se toma el más reciente (o el convertido si existe).
- Si el lead tiene `customer_id`, se considera "cliente"; si no, se considera "lead".
- Si no existe ningún lead para ese teléfono+empresa, se muestra solo el teléfono sin badge de lead.
- El permiso requerido es únicamente **Listar** (`sales.conversations.list`); no hay crear/editar/eliminar.
- La lista es paginada (por defecto 25 conversaciones por página). Al paginar, la selección activa se limpia.

### Datos mostrados en el listado (columna izquierda)

| Dato              | Origen                                                                 |
| ----------------- | ---------------------------------------------------------------------- |
| Nombre / teléfono | `customer.full_name` si tiene cliente, sino `lead.phone`               |
| Tipo              | "Cliente" si `lead.customer_id` no nulo, "Lead [status]" si es lead    |
| Source            | `chatbot_conversations.source` (source de inicio)                      |
| Última actividad  | `max(chatbot_messages.created_at)` o `chatbot_conversations.created_at`|
| Preview           | Contenido del último mensaje, recortado a 80 chars                     |

### Datos mostrados en el detalle (panel derecho)

| Dato              | Origen                                                                            |
| ----------------- | --------------------------------------------------------------------------------- |
| Encabezado        | Nombre/teléfono, badges de tipo y source principal, link a cliente si aplica      |
| Mensajes          | `chatbot_messages` ordenados por `created_at` ASC                                 |
| Burbuja usuario   | `role = 'user'`; alineada derecha                                                 |
| Burbuja agente    | `role = 'assistant'`; alineada izquierda                                          |
| Badge de source   | Solo si `message.source != conversation.source`; junto al mensaje                 |
| Timestamp         | `created_at` del mensaje, formato hora local                                      |

---

## ESPECIFICACIÓN TÉCNICA

### Sistema, módulo, permisos y menú

| Concepto                                               | Valor                                                                                                                            |
| ------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------- |
| Sistema (nombre)                                       | Ventas                                                                                                                           |
| Sistema (`slug`)                                       | `sales`                                                                                                                          |
| Módulo (nombre)                                        | Conversaciones                                                                                                                   |
| Segmento de módulo (`module_slug` relativo al sistema) | `conversations`                                                                                                                  |
| Slug persistido del módulo                             | `sales.conversations`                                                                                                            |
| Permisos                                               | `list` → `sales.conversations.list`                                                                                              |
| Nombre sugerido en seeder                              | Listar conversaciones                                                                                                            |
| **Alcance del menú (`menu scope`)**                    | **`Ventas`**: hoja **Conversaciones** bajo el padre `sales`. Declara `permission: 'sales.conversations.list'`.                   |

### Contexto de empresa (sesión)

Igual que el resto de módulos de Ventas: `SelectedCompanySession::selectedCompanyId($request)` para obtener el UUID de empresa; toda query filtra por `company_id`.

### Modelo de datos relevante (existente, sin cambios de esquema)

```
chatbot_conversations
  id              uuid PK
  company_id      uuid FK → configuration_companies
  phone           string(30)
  agent_conversation_id  string(36) UNIQUE
  source          string  — enum: web | whatsapp | facebook | instagram
  is_active       boolean
  created_at / updated_at

chatbot_messages
  id              uuid PK
  chatbot_conversation_id  uuid FK → chatbot_conversations (cascade delete)
  role            string  — enum: user | assistant
  source          string  — enum: web | whatsapp | facebook | instagram
  content         text
  created_at      timestamp

sales_leads
  id              uuid PK
  company_id      uuid FK
  phone           string(40)
  source          string
  status          string
  customer_id     uuid nullable FK → sales_customers

sales_customers
  id              uuid PK
  company_id      uuid FK
  full_name       string
  phone           string
```

**No se requiere migración nueva.** La relación conversación ↔ lead se resuelve en la query (join por `phone` + `company_id`).

### Rutas

```php
// routes/sales.php — dentro del grupo con auth + EnsureCompanySelected

Route::get('conversations', [ConversationsController::class, 'index'])
    ->name('conversations.index');

Route::get('conversations/{conversation}', [ConversationsController::class, 'show'])
    ->name('conversations.show');
```

La pantalla es un **split panel en una sola página Inertia** (`sales/conversations/index.tsx`). La conversación seleccionada se pasa como prop desde el servidor:

- `GET /sales/conversations` → renderiza `sales/conversations/index` con `conversations` (paginado) y `selected = null`.
- `GET /sales/conversations/{conversation}` → renderiza la misma vista con `conversations` (misma página del listado) y `selected` con el detalle completo (mensajes incluidos).

De este modo la URL es compartible y el servidor hace todo el trabajo pesado.

### Controlador

**`app/Http/Controllers/Sales/ConversationsController.php`**

```
index($request)
  - authorize 'viewAny' ConversationsPolicy
  - companyId desde sesión
  - paginar ChatbotConversation::forCompany()
      ->withLastMessage()         ← subquery o join
      ->withLeadInfo()            ← left join sales_leads por phone + company_id (el más reciente)
      ->withCustomerInfo()        ← left join sales_customers si lead tiene customer_id
      ->orderByLastActivity()     ← max(messages.created_at) DESC o conversations.updated_at DESC
  - render 'sales/conversations/index' con:
      conversations: paginado con campos resumidos
      selected: null

show($request, ChatbotConversation $conversation)
  - authorize 'view' $conversation  (misma policy, verifica company_id)
  - cargar $conversation->messages (ordenados ASC)
  - resolver lead y customer para el encabezado
  - render 'sales/conversations/index' con:
      conversations: misma query paginada (misma página que tiene la conversación seleccionada)
      selected: objeto con encabezado + mensajes
```

### Actions

**`app/Actions/Sales/Conversations/ListConversationsAction.php`**
- Recibe `companyId` y filtros (búsqueda por teléfono, source, tipo).
- Retorna `LengthAwarePaginator` de `ChatbotConversation` con datos enriquecidos.

**`app/Actions/Sales/Conversations/GetConversationDetailAction.php`**
- Recibe `ChatbotConversation` ya autenticada.
- Carga mensajes y datos del lead/cliente.
- Retorna array listo para serializar al frontend.

### Policy

**`app/Policies/Sales/ConversationsPolicy.php`**

| Método     | Permiso requerido           | Condición adicional                    |
| ---------- | --------------------------- | -------------------------------------- |
| `viewAny`  | `sales.conversations.list`  | —                                      |
| `view`     | `sales.conversations.list`  | `$conversation->company_id` == sesión  |

Registrar en `AuthServiceProvider`: `ChatbotConversation::class => ConversationsPolicy::class`.

### Enums de source (ya existentes)

- `App\Enums\ChatbotSource` — `Web`, `Whatsapp`, `Facebook`, `Instagram`
- `App\Enums\ChatbotMessageRole` — `User`, `Assistant`

### Tipos de frontend (TypeScript)

```ts
// types.ts
export type ConversationListItem = {
    id: string;
    phone: string;
    contact_name: string;        // customer.full_name ?? phone
    contact_type: 'customer' | 'lead' | 'unknown';
    lead_status: string | null;  // solo si contact_type === 'lead'
    customer_id: string | null;
    source: string;              // source de la conversación
    last_message_preview: string | null;
    last_activity_at: string;    // ISO 8601
};

export type ConversationMessage = {
    id: string;
    role: 'user' | 'assistant';
    source: string;
    content: string;
    created_at: string;
};

export type ConversationDetail = {
    id: string;
    phone: string;
    contact_name: string;
    contact_type: 'customer' | 'lead' | 'unknown';
    lead_status: string | null;
    customer_id: string | null;
    source: string;
    messages: ConversationMessage[];
};

export type ConversationsIndexPageProps = {
    conversations: PaginatedList<ConversationListItem>;
    selected: ConversationDetail | null;
};
```

### Estructura de archivos de frontend

```
resources/js/pages/sales/conversations/
  index.tsx              ← página principal con split panel
  types.ts               ← tipos TypeScript
  conversation-list.tsx  ← componente de lista (panel izquierdo)
  conversation-item.tsx  ← ítem individual del listado
  conversation-detail.tsx ← panel derecho con mensajes
  message-bubble.tsx     ← burbuja individual de mensaje
  source-badge.tsx       ← badge reutilizable de source (icon + label)
  labels.ts              ← etiquetas y constantes de source/status

resources/js/routes/sales/conversations/
  index.ts               ← generado por Wayfinder
```

### Layout de la página (split panel)

```
┌──────────────────────────────────────────────────────┐
│ [Encabezado de la sección: "Conversaciones"]          │
├──────────────────┬───────────────────────────────────┤
│ Panel izquierdo  │ Panel derecho                      │
│ w-80 lg:w-96     │ flex-1                             │
│ border-r         │                                    │
│                  │ [Vacío si no hay selección]         │
│ [Lista scroll]   │ [Encabezado de conversación]        │
│  - item 1 ●      │  - nombre, badges, link            │
│  - item 2        │                                    │
│  - item 3        │ [Mensajes scroll]                   │
│  ...             │  usuario →  "hola quiero..."        │
│                  │  ← agente  "Claro, te ayudo..."     │
│ [Paginación]     │  usuario →  "¿tienen envío?"       │
└──────────────────┴───────────────────────────────────┘
```

- El panel izquierdo tiene scroll propio y altura calculada (`h-[calc(100vh-header)]`).
- El panel derecho también tiene scroll propio; los mensajes hacen `scrollIntoView` al último al cargar.
- En móvil: la lista ocupa pantalla completa; al seleccionar, el panel derecho cubre la pantalla con un botón "← Volver".

### Source badge

Cada source tiene un ícono y color:

| Source    | Ícono sugerido (lucide) | Color de badge         |
| --------- | ----------------------- | ---------------------- |
| web       | `Globe`                 | gris neutro            |
| whatsapp  | `MessageCircle`         | verde                  |
| facebook  | `Facebook` / `Share2`   | azul                   |
| instagram | `Instagram` / `Camera`  | rosa/violeta           |

### Navegación

En `resources/js/nav.ts`, bajo el grupo **Ventas**, agregar:

```ts
{
    title: 'Conversaciones',
    href: '/sales/conversations',
    icon: MessageSquareText,          // lucide-react
    permission: 'sales.conversations.list',
}
```

### Seeders

Registrar en el seeder de módulos y permisos:

- **Module**: sistema `sales`, slug `sales.conversations`, nombre `"Conversaciones"`.
- **Permission**: `sales.conversations.list`, nombre `"Listar conversaciones"`.

### Archivos involucrados

| Capa                           | Ruta                                                                              |
| ------------------------------ | --------------------------------------------------------------------------------- |
| Rutas                          | `routes/sales.php`                                                                |
| Controlador                    | `app/Http/Controllers/Sales/ConversationsController.php`                          |
| Actions                        | `app/Actions/Sales/Conversations/ListConversationsAction.php`                     |
|                                | `app/Actions/Sales/Conversations/GetConversationDetailAction.php`                 |
| Policy                         | `app/Policies/Sales/ConversationsPolicy.php`                                      |
| Request                        | `app/Http/Requests/Sales/ConversationListRequest.php`                             |
| Vista (página)                 | `resources/js/pages/sales/conversations/index.tsx`                                |
| Componentes                    | `conversation-list.tsx`, `conversation-item.tsx`, `conversation-detail.tsx`       |
|                                | `message-bubble.tsx`, `source-badge.tsx`, `labels.ts`, `types.ts`                 |
| Rutas Wayfinder                | `resources/js/routes/sales/conversations/index.ts`                                |
| Navegación                     | `resources/js/nav.ts`: añadir entrada bajo grupo `sales`                          |
| Seeder                         | Registrar módulo `sales.conversations` y permiso `sales.conversations.list`       |
| AuthServiceProvider            | Registrar `ChatbotConversation::class => ConversationsPolicy::class`              |

### Consideraciones técnicas

- **Multi-tenant:** toda query filtra por `company_id` de sesión. El route binding de `ChatbotConversation` debe validar que pertenece a la empresa en sesión (similar al `resolveRouteBinding` de Lead/Customer), o verificarse en el policy.
- **Join lead ↔ conversación:** no hay FK explícita entre tablas; el join se hace en la Action por `chatbot_conversations.phone = sales_leads.phone AND sales_leads.company_id = ?`. Si hay múltiples leads para ese teléfono, tomar el de mayor prioridad: primero el `convertido`, luego el más reciente.
- **Sin cambio de esquema:** no se agrega ninguna columna ni FK nueva. La relación es lógica, resuelta en consulta.
- **Paginación del listado:** al navegar al detalle de una conversación (`show`), el listado debe mostrarse en la misma página donde aparece esa conversación. La Action del índice acepta un parámetro `page` opcional.
- **No hay búsqueda en v1**, pero la Action debe aceptar el parámetro para no tener que refactorizar al añadirla.
- **Rendimiento:** usar `withCount` o subquery para `last_activity_at` y `last_message_preview`; evitar N+1 con `with('messages')` solo en el detalle, no en el listado.
- Ejecutar generación de Wayfinder después de añadir las rutas.
