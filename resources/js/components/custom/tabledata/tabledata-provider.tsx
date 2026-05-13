import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Tabledata } from '@/components/custom/tabledata/tabledata';
import { TabledataColumnVisibilityProvider } from '@/components/custom/tabledata/tabledata-column-visibility-context';
import { TabledataListShell } from '@/components/custom/tabledata/tabledata-list-shell';
import type {
    TabledataColumn,
    TabledataProps,
} from '@/components/custom/tabledata/tabledata.types';
import {
    useTabledataListPage
    
    
    
    
} from '@/components/custom/tabledata/use-tabledata-list-inertia';
import type {TabledataListPageProps, TabledataListPageViewModel, TabledataListStandardDraft, UseTabledataListPageParams} from '@/components/custom/tabledata/use-tabledata-list-inertia';
import type { PaginatedListFilters } from '@/types/list-filters';

export type TabledataListModuleConfig = {
    storageKey: string;
    searchPlaceholder?: string;
};

export function pickTabledataListShellConfig<
    T extends TabledataListModuleConfig,
>(config: T): TabledataListModuleConfig {
    return {
        storageKey: config.storageKey,
        searchPlaceholder: config.searchPlaceholder,
    };
}

export type TabledataProviderProps<
    TRow,
    TFilters extends PaginatedListFilters,
    TDraft extends Record<string, unknown> & TabledataListStandardDraft,
> = {
    listConfig: TabledataListModuleConfig;
    listInertia: Omit<
        UseTabledataListPageParams<TRow, TFilters, TDraft>,
        'page' | 'perPageStorageKey'
    >;
    columns: TabledataColumn<TRow>[];
    toolbar?: ReactNode | ((list: TabledataListPageViewModel<TRow, TDraft>) => ReactNode);
    searchInputId?: string;
    tableContainerClassName?: string;
    className?: string;
} & Omit<
    TabledataProps<TRow>,
    'data' | 'columns' | 'sortLink' | 'activeSort' | 'activeDirection'
>;

export function TabledataProvider<
    TRow,
    TFilters extends PaginatedListFilters,
    TDraft extends Record<string, unknown> & TabledataListStandardDraft,
>({
    listConfig,
    listInertia,
    columns,
    toolbar,
    searchInputId,
    tableContainerClassName,
    className,
    ...tableProps
}: TabledataProviderProps<TRow, TFilters, TDraft>) {
    const page = usePage().props as unknown as TabledataListPageProps<
        TRow,
        TFilters
    >;

    const list = useTabledataListPage<TRow, TFilters, TDraft>({
        page,
        ...listInertia,
        perPageStorageKey: listConfig.storageKey,
    });

    const toolbarNode =
        typeof toolbar === 'function' ? toolbar(list) : toolbar;

    return (
        <TabledataColumnVisibilityProvider
            storageKey={listConfig.storageKey}
            columns={columns}
        >
            <TabledataListShell
                toolbar={toolbarNode}
                searchValue={list.filters.search}
                onSearchChange={(value) => list.setFilter('search', value)}
                searchPlaceholder={listConfig.searchPlaceholder}
                searchInputId={searchInputId}
                paginator={list.data}
                onPerPageChange={list.applyPerPage}
                tableContainerClassName={tableContainerClassName}
                className={className}
            >
                <Tabledata
                    data={list.data.data}
                    columns={columns}
                    sortLink={list.sortLink}
                    activeSort={list.sort}
                    activeDirection={list.direction}
                    {...tableProps}
                />
            </TabledataListShell>
        </TabledataColumnVisibilityProvider>
    );
}
