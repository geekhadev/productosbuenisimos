import { Columns3 } from 'lucide-react';
import { useMemo   } from 'react';
import type {Dispatch, SetStateAction} from 'react';
import {
    TABLEDATA_COLUMN_VISIBILITY_MENU_TITLE,
    TABLEDATA_COLUMN_VISIBILITY_TRIGGER_LABEL,
} from '@/components/custom/tabledata/config';
import {
    isColumnHideable,
    tabledataApplyColumnHidden,
    tabledataPruneHiddenKeys,
} from '@/components/custom/tabledata/tabledata-column-visibility';
import { useTabledataColumnVisibilityOptional } from '@/components/custom/tabledata/tabledata-column-visibility-context';
import type { TabledataColumn } from '@/components/custom/tabledata/tabledata.types';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export type TabledataColumnPickerProps<T> = {
    /**
     * Con `TabledataColumnVisibilityProvider`, no hace falta pasar `columns`, `hiddenKeys` ni `setHiddenKeys`.
     */
    columns?: TabledataColumn<T>[];
    hiddenKeys?: Set<string>;
    setHiddenKeys?: Dispatch<SetStateAction<Set<string>>>;
    /** Por defecto `true`; en `false` no se renderiza nada. */
    enabled?: boolean;
    triggerLabel?: string;
    menuTitle?: string;
};

/**
 * Menú para mostrar u ocultar columnas (`hideable !== false`).
 * Colócalo junto a filtros; con `TabledataColumnVisibilityProvider` no necesitas pasar estado.
 */
export function TabledataColumnPicker<T>({
    columns: columnsProp,
    hiddenKeys: hiddenKeysProp,
    setHiddenKeys: setHiddenKeysProp,
    enabled = true,
    triggerLabel = TABLEDATA_COLUMN_VISIBILITY_TRIGGER_LABEL,
    menuTitle = TABLEDATA_COLUMN_VISIBILITY_MENU_TITLE,
}: TabledataColumnPickerProps<T> = {}) {
    const ctx = useTabledataColumnVisibilityOptional();
    const columns = columnsProp ?? ctx?.columns;
    const hiddenKeys = hiddenKeysProp ?? ctx?.hiddenKeys;
    const setHiddenKeys = setHiddenKeysProp ?? ctx?.setHiddenKeys;

    const hideableColumns = useMemo(
        () => (columns ?? []).filter((c) => isColumnHideable(c)),
        [columns],
    );

    const prunedHiddenKeys = useMemo(() => {
        if (!columns || !hiddenKeys) {
            return new Set<string>();
        }

        return tabledataPruneHiddenKeys(columns, hiddenKeys);
    }, [columns, hiddenKeys]);

    if (
        !enabled ||
        !columns ||
        !hiddenKeys ||
        !setHiddenKeys ||
        hideableColumns.length === 0
    ) {
        return null;
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    type="button"
                    variant="outline"
                    className="gap-1.5"
                >
                    <Columns3 className="size-4" />
                    {triggerLabel}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-56">
                <DropdownMenuLabel>{menuTitle}</DropdownMenuLabel>
                <DropdownMenuSeparator />
                {hideableColumns.map((col) => (
                    <DropdownMenuCheckboxItem
                        key={col.key}
                        checked={!prunedHiddenKeys.has(col.key)}
                        onSelect={(e) => {
                            e.preventDefault();
                        }}
                        onCheckedChange={(checked) => {
                            setHiddenKeys((prev) =>
                                tabledataApplyColumnHidden(
                                    columns,
                                    prev,
                                    col.key,
                                    !checked,
                                ),
                            );
                        }}
                    >
                        {col.label}
                    </DropdownMenuCheckboxItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
