import type { FormSelectOption } from '@/components/custom/form-select';

// ---------------------------------------------------------------------------
// Paginación y query de listados
// ---------------------------------------------------------------------------

/** Default cuando no viene `per_page` en la query; alinear con `InteractsWithPaginatedListQuery` (PHP). */
export const TABLEDATA_DEFAULT_PER_PAGE = 20;

const TABLEDATA_PER_PAGE_VALUES = [20, 50, 100] as const;

export type TabledataPerPageOption = (typeof TABLEDATA_PER_PAGE_VALUES)[number];

/**
 * Valores permitidos en el selector de `per_page` (alinear con el pie de tabla).
 */
export function isTabledataPerPageOption(n: number): n is TabledataPerPageOption {
    return (TABLEDATA_PER_PAGE_VALUES as readonly number[]).includes(n);
}

/**
 * Opciones estándar de `per_page` para listados con {@link TabledataPaginationFooter}.
 */
export const TABLEDATA_PER_PAGE_OPTIONS: FormSelectOption[] =
    TABLEDATA_PER_PAGE_VALUES.map((n) => ({
        id: String(n),
        label: String(n),
    }));

/**
 * Props Inertia (`only`) típicas en índices con colección paginada `data` y `filters`.
 */
export const TABLEDATA_LIST_INERTIA_ONLY = ['data', 'filters'] as const;

// ---------------------------------------------------------------------------
// TabledataListShell — layout
// ---------------------------------------------------------------------------

export const TABLEDATA_LIST_SHELL_ROOT_CLASS = 'space-y-3 px-4 py-3';

export const TABLEDATA_LIST_SHELL_HEADER_ROW_CLASS =
    'flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between';

export const TABLEDATA_LIST_SHELL_TOOLBAR_OUTER_CLASS =
    'flex w-full flex-col gap-3 sm:flex-row';

export const TABLEDATA_LIST_SHELL_TOOLBAR_INNER_CLASS =
    'flex w-full flex-col gap-3 sm:flex-row justify-end';

export const TABLEDATA_LIST_SHELL_SEARCH_WRAPPER_CLASS = 'max-w-48 flex-1';

export const TABLEDATA_LIST_SHELL_TABLE_BLOCK_CLASS = 'flex flex-col gap-4';

export const TABLEDATA_LIST_SHELL_SCROLL_CONTAINER_CLASS =
    'h-[calc(100vh-215px)] overflow-auto rounded-b-xl bg-card';

// ---------------------------------------------------------------------------
// TabledataSearch
// ---------------------------------------------------------------------------

export const TABLEDATA_SEARCH_DEFAULT_PLACEHOLDER = 'Buscar…';

export const TABLEDATA_SEARCH_INPUT_ID = 'tabledata-search';

// ---------------------------------------------------------------------------
// Tabla vacía y visibilidad de columnas
// ---------------------------------------------------------------------------

export const TABLEDATA_EMPTY_MESSAGE_DEFAULT = 'No hay resultados.';

export const TABLEDATA_COLUMN_VISIBILITY_TRIGGER_LABEL = 'Columnas';

export const TABLEDATA_COLUMN_VISIBILITY_MENU_TITLE = 'Columnas visibles';

// ---------------------------------------------------------------------------
// localStorage — columnas ocultas
// ---------------------------------------------------------------------------

export const TABLEDATA_HIDDEN_COLUMNS_STORAGE_PREFIX = 'tabledata:hidden-columns:';

export const TABLEDATA_PER_PAGE_STORAGE_PREFIX = 'tabledata:per-page:';

// ---------------------------------------------------------------------------
// Pie de paginación
// ---------------------------------------------------------------------------

export const TABLEDATA_PER_PAGE_SELECT_ID = 'tabledata-per-page';

export const TABLEDATA_PAGINATION_SUMMARY_EMPTY =
    'No hay resultados que mostrar';

export const TABLEDATA_PAGINATION_FOOTER_LAYOUT_CLASS =
    'flex shrink-0 flex-col gap-4 sm:flex-row sm:justify-between sm:items-center';

export const TABLEDATA_PAGINATION_FOOTER_INNER_CLASS =
    'flex min-w-0 flex-col gap-3 sm:flex-row sm:gap-6 items-center';

export const TABLEDATA_PAGINATION_FOOTER_PER_PAGE_WRAP_CLASS =
    'w-full max-w-20 shrink-0 sm:w-40 flex items-center';
