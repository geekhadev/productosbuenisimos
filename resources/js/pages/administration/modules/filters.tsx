import { Check, RotateCcw, X } from 'lucide-react';
import type { ReactNode } from 'react';
import { FilterDropdown } from '@/components/custom/filter-dropdown';
import type { FormSelectOption } from '@/components/custom/form-select';
import { FormSelect } from '@/components/custom/form-select';
import type { TabledataListPageViewModel } from '@/components/custom/tabledata';
import { Button } from '@/components/ui/button';
import type {
    Module,
    ModulesIndexFiltersDraftFull,
    SystemOption,
} from './types';

type ModulesIndexFiltersProps = Pick<
    TabledataListPageViewModel<Module, ModulesIndexFiltersDraftFull>,
    'filters' | 'setFilter' | 'applyFilters' | 'resetFilters'
> & {
    systems: SystemOption[];
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

export function ModulesIndexFilters({
    filters,
    setFilter,
    applyFilters,
    resetFilters,
    systems,
}: ModulesIndexFiltersProps) {
    const systemOptions: FormSelectOption[] = [
        { id: '', label: 'Todos los sistemas' },
        ...systems.map((s) => ({
            id: String(s.id),
            label: s.name,
        })),
    ];

    return (
        <FilterDropdown
            footer={
                <div className="mt-4 flex flex-wrap gap-2 border-t border-border pt-4">
                    <Button type="button" onClick={applyFilters}>
                        <Check />
                        Aplicar
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={resetFilters}
                    >
                        <RotateCcw />
                        Reiniciar
                    </Button>
                </div>
            }
        >
            <FilterRowWithClear
                canClear={filters.system_id.trim() !== ''}
                onClear={() => setFilter('system_id', '')}
                clearLabel="Limpiar filtro de sistema"
            >
                <FormSelect
                    label="Sistema"
                    placeholder=""
                    options={systemOptions}
                    selectProps={{
                        id: 'filter-system_id',
                        name: 'system_id',
                        value: filters.system_id,
                        onChange: (e) =>
                            setFilter('system_id', e.target.value),
                    }}
                />
            </FilterRowWithClear>
        </FilterDropdown>
    );
}
