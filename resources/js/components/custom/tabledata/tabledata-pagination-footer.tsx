import type { FormSelectOption } from '@/components/custom/form-select';
import { FormSelect } from '@/components/custom/form-select';
import {
    TABLEDATA_PAGINATION_FOOTER_INNER_CLASS,
    TABLEDATA_PAGINATION_FOOTER_LAYOUT_CLASS,
    TABLEDATA_PAGINATION_FOOTER_PER_PAGE_WRAP_CLASS,
    TABLEDATA_PAGINATION_SUMMARY_EMPTY,
    TABLEDATA_PER_PAGE_OPTIONS,
    TABLEDATA_PER_PAGE_SELECT_ID,
} from '@/components/custom/tabledata/config';
import { TabledataPagination } from '@/components/custom/tabledata/tabledata-pagination';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types/pagination';

export type TabledataPaginationFooterProps<T> = {
    paginator: Paginated<T>;
    /** Por defecto {@link TABLEDATA_PER_PAGE_OPTIONS}. */
    perPageOptions?: FormSelectOption[];
    onPerPageChange: (value: string) => void;
    /** Props `only` de Inertia al navegar (p. ej. `TABLEDATA_LIST_INERTIA_ONLY` en `config.ts`). */
    only?: string[];
    className?: string;
};

function paginationSummary(
    from: number | null,
    to: number | null,
    total: number,
): string {
    if (total <= 0) {
        return TABLEDATA_PAGINATION_SUMMARY_EMPTY;
    }

    const start = from ?? 0;
    const end = to ?? 0;

    return `Mostrando ${start} a ${end} de ${total} resultados`;
}

/**
 * Barra inferior de listados Laravel: resumen “mostrando X–Y de Z”, selector de `per_page` y enlaces de página.
 */
export function TabledataPaginationFooter<T>({
    paginator,
    perPageOptions = TABLEDATA_PER_PAGE_OPTIONS,
    onPerPageChange,
    only = [],
    className,
}: TabledataPaginationFooterProps<T>) {
    const summary = paginationSummary(
        paginator.from,
        paginator.to,
        paginator.total,
    );

    return (
        <div className={cn(TABLEDATA_PAGINATION_FOOTER_LAYOUT_CLASS, className)}>
            <div className={TABLEDATA_PAGINATION_FOOTER_INNER_CLASS}>
                <p className="text-sm text-muted-foreground">{summary}</p>
                <div className={TABLEDATA_PAGINATION_FOOTER_PER_PAGE_WRAP_CLASS}>
                    <FormSelect
                        placeholder=""
                        options={perPageOptions}
                        selectProps={{
                            id: TABLEDATA_PER_PAGE_SELECT_ID,
                            value: String(paginator.per_page),
                            onChange: (e) => onPerPageChange(e.target.value),
                        }}
                    />
                </div>
            </div>
            <TabledataPagination
                paginator={paginator}
                only={only}
                className="justify-center"
            />
        </div>
    );
}
