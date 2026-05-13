import { Link } from '@inertiajs/react';
import { ChevronDown, ChevronUp, ChevronsUpDown, Columns3 } from 'lucide-react';
import { useCallback, useMemo, useState } from 'react';
import type { MouseEvent, ReactNode } from 'react';
import {
    TABLEDATA_COLUMN_VISIBILITY_MENU_TITLE,
    TABLEDATA_COLUMN_VISIBILITY_TRIGGER_LABEL,
    TABLEDATA_EMPTY_MESSAGE_DEFAULT,
} from '@/components/custom/tabledata/config';
import {
    isColumnHideable,
    tabledataApplyColumnHidden,
    tabledataPruneHiddenKeys,
} from '@/components/custom/tabledata/tabledata-column-visibility';
import { useTabledataColumnVisibilityOptional } from '@/components/custom/tabledata/tabledata-column-visibility-context';
import { TabledataEmptyRow } from '@/components/custom/tabledata/tabledata-empty-row';
import type { TabledataProps } from '@/components/custom/tabledata/tabledata.types';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { cn } from '@/lib/utils';

export type {
    TabledataColumn,
    TabledataDensity,
    TabledataProps,
    TabledataSortDirection,
} from '@/components/custom/tabledata/tabledata.types';

function cellFallback<T>(row: T, key: string): ReactNode {
    const v = (row as unknown as Record<string, unknown>)[key];

    if (v == null || v === '') {
        return '—';
    }

    if (typeof v === 'object') {
        return JSON.stringify(v);
    }

    return String(v);
}

/**
 * Tabla declarativa por columnas.
 * `sortable` por defecto es `false`; el orden usa siempre `key` como nombre de campo.
 * Sin `render`, la celda muestra `row[key]`.
 * `hideable` por columna por defecto es `true` (menú mostrar/ocultar columnas).
 */
export function Tabledata<T>({
    data,
    columns,
    sortLink,
    activeSort,
    activeDirection = 'asc',
    emptyMessage = TABLEDATA_EMPTY_MESSAGE_DEFAULT,
    getRowKey,
    density = 'normal',
    columnVisibility = true,
    columnVisibilityTriggerLabel = TABLEDATA_COLUMN_VISIBILITY_TRIGGER_LABEL,
    columnVisibilityMenuTitle = TABLEDATA_COLUMN_VISIBILITY_MENU_TITLE,
    columnHiddenKeys: columnHiddenKeysProp,
    onColumnHiddenKeysChange,
}: TabledataProps<T>) {
    const visibilityCtx = useTabledataColumnVisibilityOptional();

    const hideableColumns = useMemo(
        () => columns.filter((c) => isColumnHideable(c)),
        [columns],
    );

    const isExplicitlyControlled =
        columnHiddenKeysProp !== undefined &&
        onColumnHiddenKeysChange !== undefined &&
        visibilityCtx === null;

    const [internalHiddenKeys, setInternalHiddenKeys] = useState<Set<string>>(
        () => new Set(),
    );

    const hiddenKeys =
        visibilityCtx !== null
            ? visibilityCtx.hiddenKeys
            : isExplicitlyControlled
              ? columnHiddenKeysProp!
              : internalHiddenKeys;

    const setHiddenKeys =
        visibilityCtx !== null
            ? visibilityCtx.setHiddenKeys
            : isExplicitlyControlled
              ? onColumnHiddenKeysChange!
              : setInternalHiddenKeys;

    const prunedHiddenKeys = useMemo(
        () => tabledataPruneHiddenKeys(columns, hiddenKeys),
        [columns, hiddenKeys],
    );

    const visibleColumns = useMemo(() => {
        return columns.filter(
            (col) => !isColumnHideable(col) || !prunedHiddenKeys.has(col.key),
        );
    }, [columns, prunedHiddenKeys]);

    const setColumnHidden = useCallback(
        (key: string, hidden: boolean) => {
            setHiddenKeys((prev) =>
                tabledataApplyColumnHidden(columns, prev, key, hidden),
            );
        },
        [columns, setHiddenKeys],
    );

    const [selectedRowKey, setSelectedRowKey] = useState<string | number | null>(
        null,
    );

    const selectedKeysInData = useMemo(
        () => new Set(data.map((row) => getRowKey(row))),
        [data, getRowKey],
    );

    const resolvedSelectedKey =
        selectedRowKey !== null && selectedKeysInData.has(selectedRowKey)
            ? selectedRowKey
            : null;

    const handleRowClick = useCallback(
        (event: MouseEvent<HTMLTableRowElement>, rowKey: string | number) => {
            const target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            if (
                target.closest(
                    'a, button, input, select, textarea, label, [role="checkbox"]',
                )
            ) {
                return;
            }

            setSelectedRowKey((prev) => (prev === rowKey ? null : rowKey));
        },
        [],
    );

    const showInternalColumnPicker =
        columnVisibility === true &&
        !isExplicitlyControlled &&
        visibilityCtx === null &&
        hideableColumns.length > 0;

    const colSpan = visibleColumns.length;
    const isCompact = density === 'compact';
    const densityHeadClass = isCompact
        ? 'h-auto min-h-7 px-1.5 py-1.5 text-xs leading-tight sm:text-sm'
        : undefined;
    const densityCellClass = isCompact
        ? 'px-1.5 py-1 text-sm leading-snug'
        : undefined;
    const sortLinkIconClass = isCompact ? 'size-3.5 shrink-0' : 'size-4 shrink-0';

    const internalPicker = showInternalColumnPicker ? (
        <div className="flex justify-end px-1">
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        className="gap-1.5"
                    >
                        <Columns3 className="size-4" />
                        {columnVisibilityTriggerLabel}
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-56">
                    <DropdownMenuLabel>
                        {columnVisibilityMenuTitle}
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    {hideableColumns.map((col) => (
                        <DropdownMenuCheckboxItem
                            key={col.key}
                            checked={!prunedHiddenKeys.has(col.key)}
                            onSelect={(e) => {
                                e.preventDefault();
                            }}
                            onCheckedChange={(checked) =>
                                setColumnHidden(col.key, !checked)
                            }
                        >
                            {col.label}
                        </DropdownMenuCheckboxItem>
                    ))}
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    ) : null;

    return (
        <div className={internalPicker ? 'space-y-2' : undefined}>
            {internalPicker}
            <Table>
                <TableHeader>
                    <TableRow>
                        {visibleColumns.map((col) => {
                            const isSortable = col.sortable === true && sortLink;
                            const isActive = Boolean(
                                isSortable &&
                                    activeSort !== undefined &&
                                    activeSort === col.key,
                            );

                            return (
                                <TableHead
                                    key={col.key}
                                    className={cn(
                                        'sticky top-0 z-10 bg-blue-50 shadow-sm dark:bg-blue-950/40',
                                        col.headerClassName,
                                        densityHeadClass,
                                    )}
                                >
                                    {isSortable ? (
                                        <Link
                                            href={sortLink(col.key)}
                                            className={cn(
                                                'flex w-full min-w-0 items-center justify-between gap-2 text-foreground no-underline hover:underline-offset-4',
                                                isCompact ? 'py-0.5' : 'py-1',
                                            )}
                                            aria-sort={
                                                isActive
                                                    ? activeDirection === 'asc'
                                                        ? 'ascending'
                                                        : 'descending'
                                                    : undefined
                                            }
                                        >
                                            <span className="truncate font-medium">
                                                {col.label}
                                            </span>
                                            <span
                                                className={cn(
                                                    'flex shrink-0 items-center transition-opacity',
                                                    isActive
                                                        ? 'text-foreground opacity-100'
                                                        : 'text-muted-foreground opacity-45 hover:opacity-80',
                                                )}
                                                aria-hidden
                                            >
                                                {isActive ? (
                                                    activeDirection === 'asc' ? (
                                                        <ChevronUp
                                                            className={
                                                                sortLinkIconClass
                                                            }
                                                        />
                                                    ) : (
                                                        <ChevronDown
                                                            className={
                                                                sortLinkIconClass
                                                            }
                                                        />
                                                    )
                                                ) : (
                                                    <ChevronsUpDown
                                                        className={
                                                            sortLinkIconClass
                                                        }
                                                    />
                                                )}
                                            </span>
                                        </Link>
                                    ) : (
                                        col.label
                                    )}
                                </TableHead>
                            );
                        })}
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {data.length === 0 ? (
                        <TabledataEmptyRow
                            colSpan={Math.max(colSpan, 1)}
                            density={density}
                            message={emptyMessage}
                        />
                    ) : (
                        data.map((row) => {
                            const rowKey = getRowKey(row);
                            const isSelected = resolvedSelectedKey === rowKey;

                            return (
                                <TableRow
                                    key={rowKey}
                                    data-state={isSelected ? 'selected' : undefined}
                                    aria-selected={isSelected}
                                    onClick={(e) => handleRowClick(e, rowKey)}
                                >
                                    {visibleColumns.map((col) => (
                                    <TableCell
                                        key={col.key}
                                        className={cn(
                                            col.className,
                                            densityCellClass,
                                        )}
                                    >
                                        {col.render !== undefined
                                            ? col.render(row)
                                            : cellFallback(row, col.key)}
                                    </TableCell>
                                    ))}
                                </TableRow>
                            );
                        })
                    )}
                </TableBody>
            </Table>
        </div>
    );
}
