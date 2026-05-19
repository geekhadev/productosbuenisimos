import { Check, RotateCcw, X } from 'lucide-react';
import type { ReactNode } from 'react';
import { FilterDropdown } from '@/components/custom/filter-dropdown';
import { FormDatePickerField } from '@/components/custom/form-date-picker-field';
import type { TabledataListPageViewModel } from '@/components/custom/tabledata';
import { Button } from '@/components/ui/button';
import type {
    OrderRow,
    OrdersIndexFiltersDraftFull,
} from '@/pages/sales/orders/types';

type OrdersIndexFiltersProps = Pick<
    TabledataListPageViewModel<OrderRow, OrdersIndexFiltersDraftFull>,
    'filters' | 'setFilter' | 'applyFilters' | 'resetFilters'
>;

function FilterRowWithClear({
    children,
    canClear,
    onClear,
    clearLabel,
}: {
    children: ReactNode;
    canClear: boolean;
    onClear: () => void;
    clearLabel: string;
}) {
    return (
        <div className="flex items-end gap-2">
            <div className="min-w-0 flex-1">{children}</div>
            <Button
                type="button"
                variant="ghost"
                size="icon"
                className="size-9 shrink-0 text-muted-foreground hover:text-foreground disabled:opacity-40"
                disabled={!canClear}
                onClick={onClear}
                aria-label={clearLabel}
            >
                <X className="size-4" aria-hidden />
            </Button>
        </div>
    );
}

export function OrdersIndexFilters({
    filters,
    setFilter,
    applyFilters,
    resetFilters,
}: OrdersIndexFiltersProps) {
    return (
        <FilterDropdown
            footer={
                <div className="mt-4 flex flex-wrap gap-2 border-t border-border pt-4">
                    <Button type="button" onClick={applyFilters}>
                        <Check />
                        Aplicar
                    </Button>
                    <Button type="button" variant="outline" onClick={resetFilters}>
                        <RotateCcw />
                        Reiniciar
                    </Button>
                </div>
            }
        >
            <div className="grid gap-4 sm:grid-cols-2">
                <FilterRowWithClear
                    canClear={filters.date_from.trim() !== ''}
                    onClear={() => setFilter('date_from', '')}
                    clearLabel="Limpiar fecha desde"
                >
                    <FormDatePickerField
                        label="Desde"
                        value={filters.date_from}
                        onChange={(value) => setFilter('date_from', value)}
                        id="filter-date-from"
                    />
                </FilterRowWithClear>
                <FilterRowWithClear
                    canClear={filters.date_to.trim() !== ''}
                    onClear={() => setFilter('date_to', '')}
                    clearLabel="Limpiar fecha hasta"
                >
                    <FormDatePickerField
                        label="Hasta"
                        value={filters.date_to}
                        onChange={(value) => setFilter('date_to', value)}
                        id="filter-date-to"
                    />
                </FilterRowWithClear>
            </div>
        </FilterDropdown>
    );
}
