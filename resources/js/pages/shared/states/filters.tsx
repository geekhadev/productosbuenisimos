import { Check, RotateCcw, X } from 'lucide-react';
import type { ReactNode } from 'react';
import { FilterDropdown } from '@/components/custom/filter-dropdown';
import { FormSelect } from '@/components/custom/form-select';
import type { TabledataListPageViewModel } from '@/components/custom/tabledata';
import { Button } from '@/components/ui/button';
import type {
    CountryOption,
    State,
    StatesIndexFiltersDraftFull,
} from './types';

type StatesIndexFiltersProps = Pick<
    TabledataListPageViewModel<State, StatesIndexFiltersDraftFull>,
    'filters' | 'setFilter' | 'applyFilters' | 'resetFilters'
> & {
    countries: CountryOption[];
};

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

export function StatesIndexFilters({
    filters,
    setFilter,
    applyFilters,
    resetFilters,
    countries,
}: StatesIndexFiltersProps) {
    const options = countries.map((country) => ({
        id: country.id,
        label: country.name,
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
                canClear={filters.country_id.trim() !== ''}
                onClear={() => setFilter('country_id', '')}
                clearLabel="Limpiar filtro de pais"
            >
                <FormSelect
                    label="Pais"
                    placeholder=""
                    options={options}
                    selectProps={{
                        id: 'filter-country_id',
                        name: 'country_id',
                        value: filters.country_id,
                        onChange: (e) => setFilter('country_id', e.target.value),
                    }}
                />
            </FilterRowWithClear>
        </FilterDropdown>
    );
}
