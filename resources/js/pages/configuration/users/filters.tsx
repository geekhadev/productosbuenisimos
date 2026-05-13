import { Check, RotateCcw, X } from 'lucide-react';
import type { ReactNode } from 'react';
import { FilterDropdown } from '@/components/custom/filter-dropdown';
import type { FormSelectOption } from '@/components/custom/form-select';
import { FormSelect } from '@/components/custom/form-select';
import type { TabledataListPageViewModel } from '@/components/custom/tabledata';
import { Button } from '@/components/ui/button';
import type {
    CompanyOption,
    UserListRow,
    UsersIndexFiltersDraftFull,
} from './types';

type UsersIndexFiltersProps = Pick<
    TabledataListPageViewModel<UserListRow, UsersIndexFiltersDraftFull>,
    'filters' | 'setFilter' | 'applyFilters' | 'resetFilters'
> & {
    companies: CompanyOption[];
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

const TYPE_OPTIONS: FormSelectOption[] = [
    { id: '', label: 'Todos los tipos' },
    { id: 'root', label: 'Root' },
    { id: 'owner', label: 'Owner' },
    { id: 'user', label: 'Usuario' },
];

export function UsersIndexFilters({
    filters,
    setFilter,
    applyFilters,
    resetFilters,
    companies,
}: UsersIndexFiltersProps) {
    const companyOptions: FormSelectOption[] = [
        { id: '', label: 'Todas las empresas' },
        ...companies.map((c) => ({
            id: String(c.id),
            label: c.name,
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
                canClear={filters.type.trim() !== ''}
                onClear={() => setFilter('type', '')}
                clearLabel="Limpiar filtro de tipo"
            >
                <FormSelect
                    label="Tipo"
                    placeholder=""
                    options={TYPE_OPTIONS}
                    selectProps={{
                        id: 'filter-type',
                        name: 'type',
                        value: filters.type,
                        onChange: (e) => setFilter('type', e.target.value),
                    }}
                />
            </FilterRowWithClear>
            <div className="mt-3">
                <FilterRowWithClear
                    canClear={filters.company_id.trim() !== ''}
                    onClear={() => setFilter('company_id', '')}
                    clearLabel="Limpiar filtro de empresa"
                >
                    <FormSelect
                        label="Empresa"
                        placeholder=""
                        options={companyOptions}
                        selectProps={{
                            id: 'filter-company_id',
                            name: 'company_id',
                            value: filters.company_id,
                            onChange: (e) =>
                                setFilter('company_id', e.target.value),
                        }}
                    />
                </FilterRowWithClear>
            </div>
        </FilterDropdown>
    );
}
