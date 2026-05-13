import type { Paginated } from '@/types/pagination';

export type TabledataPaginationSlice = Pick<
    Paginated<unknown>,
    'last_page' | 'links'
>;

/**
 * Derivados del paginador de Laravel para decidir si mostrar UI de páginas.
 * Usar junto con {@link TabledataPagination} u otro componente que renderice `links`.
 */
export function useTabledataPagination(paginator: TabledataPaginationSlice): {
    shouldRender: boolean;
    links: Paginated<unknown>['links'];
} {
    return {
        shouldRender: paginator.last_page > 1,
        links: paginator.links,
    };
}
