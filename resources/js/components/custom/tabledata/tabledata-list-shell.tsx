import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types/pagination';
import {
    TABLEDATA_LIST_INERTIA_ONLY,
    TABLEDATA_LIST_SHELL_HEADER_ROW_CLASS,
    TABLEDATA_LIST_SHELL_ROOT_CLASS,
    TABLEDATA_LIST_SHELL_SCROLL_CONTAINER_CLASS,
    TABLEDATA_LIST_SHELL_SEARCH_WRAPPER_CLASS,
    TABLEDATA_LIST_SHELL_TABLE_BLOCK_CLASS,
    TABLEDATA_LIST_SHELL_TOOLBAR_INNER_CLASS,
    TABLEDATA_LIST_SHELL_TOOLBAR_OUTER_CLASS,
    TABLEDATA_SEARCH_DEFAULT_PLACEHOLDER,
    TABLEDATA_SEARCH_INPUT_ID,
} from './config';
import { TabledataColumnPicker } from './tabledata-column-picker';
import { TabledataPaginationFooter } from './tabledata-pagination-footer';
import { TabledataSearch } from './tabledata-search';

export type TabledataListShellProps<T> = {
    toolbar?: ReactNode;
    searchValue: string;
    onSearchChange: (value: string) => void;
    searchPlaceholder?: string;
    searchInputId?: string;
    children: ReactNode;
    paginator: Paginated<T>;
    onPerPageChange: (value: string) => void;
    tableContainerClassName?: string;
    className?: string;
};

export function TabledataListShell<T>({
    toolbar,
    searchValue,
    onSearchChange,
    searchPlaceholder = TABLEDATA_SEARCH_DEFAULT_PLACEHOLDER,
    searchInputId = TABLEDATA_SEARCH_INPUT_ID,
    children,
    paginator,
    onPerPageChange,
    tableContainerClassName,
    className,
}: TabledataListShellProps<T>) {
    return (
        <div className={cn(TABLEDATA_LIST_SHELL_ROOT_CLASS, className)}>
            <div className={TABLEDATA_LIST_SHELL_HEADER_ROW_CLASS}>
                <div className={TABLEDATA_LIST_SHELL_TOOLBAR_OUTER_CLASS}>
                    <div className={TABLEDATA_LIST_SHELL_SEARCH_WRAPPER_CLASS}>
                        <TabledataSearch
                            value={searchValue}
                            onChange={onSearchChange}
                            placeholder={searchPlaceholder}
                            id={searchInputId}
                        />
                    </div>
                    <TabledataColumnPicker />
                </div>
                <div className={TABLEDATA_LIST_SHELL_TOOLBAR_INNER_CLASS}>
                    {toolbar}
                </div>
            </div>

            <div className={TABLEDATA_LIST_SHELL_TABLE_BLOCK_CLASS}>
                <div
                    className={cn(
                        TABLEDATA_LIST_SHELL_SCROLL_CONTAINER_CLASS,
                        tableContainerClassName,
                    )}
                >
                    {children}
                </div>
                <TabledataPaginationFooter
                    paginator={paginator}
                    onPerPageChange={onPerPageChange}
                    only={[...TABLEDATA_LIST_INERTIA_ONLY]}
                />
            </div>
        </div>
    );
}
