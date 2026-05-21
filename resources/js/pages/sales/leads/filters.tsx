import { Check, RotateCcw, X } from 'lucide-react';
import type { ReactNode } from 'react';
import { FilterDropdown } from '@/components/custom/filter-dropdown';
import { FormSelect } from '@/components/custom/form-select';
import type { TabledataListPageViewModel } from '@/components/custom/tabledata';
import { Button } from '@/components/ui/button';
import { LEAD_SOURCE_OPTIONS, LEAD_STATUS_OPTIONS } from '@/pages/sales/leads/labels';
import type {
    LeadRow,
    LeadsIndexFiltersDraftFull,
} from '@/pages/sales/leads/types';

type LeadsIndexFiltersProps = Pick<
    TabledataListPageViewModel<LeadRow, LeadsIndexFiltersDraftFull>,
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

export function LeadsIndexFilters({
    filters,
    setFilter,
    applyFilters,
    resetFilters,
}: LeadsIndexFiltersProps) {
    const statusOptions = LEAD_STATUS_OPTIONS.map((o) => ({
        id: o.id,
        label: o.label,
    }));
    const sourceOptions = LEAD_SOURCE_OPTIONS.map((o) => ({
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
            <div className="grid gap-4 sm:grid-cols-2">
                <FilterRowWithClear
                    canClear={filters.status.trim() !== ''}
                    onClear={() => setFilter('status', '')}
                    clearLabel="Limpiar filtro de estado"
                >
                    <FormSelect
                        label="Estado"
                        placeholder=""
                        options={statusOptions}
                        selectProps={{
                            id: 'filter-status',
                            name: 'status',
                            value: filters.status,
                            onChange: (e) => setFilter('status', e.target.value),
                        }}
                    />
                </FilterRowWithClear>
                <FilterRowWithClear
                    canClear={filters.source.trim() !== ''}
                    onClear={() => setFilter('source', '')}
                    clearLabel="Limpiar filtro de fuente"
                >
                    <FormSelect
                        label="Fuente"
                        placeholder=""
                        options={sourceOptions}
                        selectProps={{
                            id: 'filter-source',
                            name: 'source',
                            value: filters.source,
                            onChange: (e) => setFilter('source', e.target.value),
                        }}
                    />
                </FilterRowWithClear>
            </div>
        </FilterDropdown>
    );
}
