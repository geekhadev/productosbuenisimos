import { Check, RotateCcw, X } from 'lucide-react';
import type { ReactNode } from 'react';
import { FilterDropdown } from '@/components/custom/filter-dropdown';
import { FormSelect } from '@/components/custom/form-select';
import type { TabledataListPageViewModel } from '@/components/custom/tabledata';
import { Button } from '@/components/ui/button';
import type {
    ProductRow,
    ProductsIndexFiltersDraftFull,
} from '@/pages/stock/products/types';

const STATUS_OPTIONS = [
    { id: '', label: 'Todos' },
    { id: 'active', label: 'Solo activos' },
    { id: 'inactive', label: 'Solo inactivos' },
] as const;

type ProductsIndexFiltersProps = Pick<
    TabledataListPageViewModel<ProductRow, ProductsIndexFiltersDraftFull>,
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

export function ProductsIndexFilters({
    filters,
    setFilter,
    applyFilters,
    resetFilters,
}: ProductsIndexFiltersProps) {
    const options = STATUS_OPTIONS.map((o) => ({
        id: o.id,
        label: o.label,
    }));

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
            <FilterRowWithClear
                canClear={filters.status.trim() !== ''}
                onClear={() => setFilter('status', '')}
                clearLabel="Limpiar filtro de estado"
            >
                <FormSelect
                    label="Estado"
                    placeholder=""
                    options={options}
                    selectProps={{
                        id: 'filter-status',
                        name: 'status',
                        value: filters.status,
                        onChange: (e) => setFilter('status', e.target.value),
                    }}
                />
            </FilterRowWithClear>
        </FilterDropdown>
    );
}
