import type { Dispatch, ReactNode, SetStateAction } from 'react';

export type TabledataSortDirection = 'asc' | 'desc';

export type TabledataDensity = 'normal' | 'compact';

export type TabledataColumn<T> = {
    label: string;
    /**
     * Nombre del campo en la fila y, si `sortable`, parámetro de ordenación en la query.
     */
    key: string;
    /** Por defecto `false`. */
    sortable?: boolean;
    /**
     * Si la columna aparece en el menú “mostrar / ocultar”.
     * Por defecto `true` (todas las columnas son ocultables salvo que se ponga `false`).
     */
    hideable?: boolean;
    headerClassName?: string;
    className?: string;
    /** Si no se define, se muestra el valor de `row[key]` en texto. */
    render?: (row: T) => ReactNode;
};

export type TabledataProps<T> = {
    data: T[];
    columns: TabledataColumn<T>[];
    sortLink?: (column: string) => string;
    /** Columna por la que ordena el listado (debe coincidir con `key` de columna sortable). */
    activeSort?: string;
    activeDirection?: TabledataSortDirection;
    emptyMessage?: ReactNode;
    getRowKey: (row: T) => string | number;
    /** `normal`: padding por defecto. `compact`: menos padding en cabeceras y celdas. */
    density?: TabledataDensity;
    /**
     * Muestra el menú integrado sobre la tabla (`hideable !== false`).
     * Con `TabledataProvider` / `TabledataColumnVisibilityProvider` el estado (y `localStorage`) lo lleva el provider;
     * entonces no pases `columnHiddenKeys` y usa `TabledataColumnPicker` sin props.
     * Sin provider: si pasas `columnHiddenKeys` y `onColumnHiddenKeysChange`, el menú integrado no se muestra.
     */
    columnVisibility?: boolean;
    /** Texto del botón del menú integrado. */
    columnVisibilityTriggerLabel?: string;
    /** Encabezado del menú integrado. */
    columnVisibilityMenuTitle?: string;
    /** Estado controlado de columnas ocultas (junto con `onColumnHiddenKeysChange`). */
    columnHiddenKeys?: Set<string>;
    onColumnHiddenKeysChange?: Dispatch<SetStateAction<Set<string>>>;
};
