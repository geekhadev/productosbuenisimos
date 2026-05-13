import { router } from '@inertiajs/react';
import {
    startTransition,
    useCallback,
    useEffect,
    useMemo,
    useRef,
    useState,
} from 'react';
import {
    TABLEDATA_LIST_INERTIA_ONLY,
    isTabledataPerPageOption,
} from '@/components/custom/tabledata/config';
import {
    tabledataListFiltersToQueryWithModuleKeys,
    tabledataMergeModuleFilterDraft,
    tabledataModuleDraftFromApplied,
} from '@/components/custom/tabledata/tabledata-list-module-filters';
import { TABLEDATA_LIST_RESET_QUERY } from '@/components/custom/tabledata/tabledata-list-query';
import type { TabledataListQueryValues } from '@/components/custom/tabledata/tabledata-list-query';
import {
    readTabledataStoredPerPage,
    writeTabledataStoredPerPage,
    writeTabledataStoredPerPageDefault,
} from '@/components/custom/tabledata/tabledata-per-page-storage';
import type { PaginatedListFilters } from '@/types/list-filters';
import type { Paginated } from '@/types/pagination';

/** Construye la URL del índice del listado (p. ej. Wayfinder `index.url({ query })`). */
export type TabledataIndexUrlBuilder = (query: TabledataListQueryValues) => string;

export type TabledataFiltersToQuery<TFilters extends PaginatedListFilters> = (
    filters: TFilters,
    overrides?: Record<string, unknown>,
) => TabledataListQueryValues;

export type UseTabledataListInertiaParams<TFilters extends PaginatedListFilters> = {
    appliedFilters: TFilters;
    filtersToQuery: TabledataFiltersToQuery<TFilters>;
    indexUrl: TabledataIndexUrlBuilder;
    /** Por defecto `TABLEDATA_LIST_INERTIA_ONLY`. */
    inertiaOnly?: readonly string[];
    /**
     * Si se define, el selector `per_page` persiste en `localStorage` bajo esta clave
     * (p. ej. la misma `storageKey` del módulo que usa columnas ocultas).
     */
    perPageStorageKey?: string;
};

export type TabledataListVisitOptions = {
    /** Para búsqueda con debounce u otras visitas que no deben añadir entrada al historial. */
    replace?: boolean;
};

export type TabledataListInertiaNavigation = {
    /**
     * GET al índice del listado con query ya serializada (`preserveState` + `only` estándar).
     */
    visitIndex: (
        query: TabledataListQueryValues,
        options?: TabledataListVisitOptions,
    ) => void;
    applyPerPage: (nextPerPage: string) => void;
    sortLink: (column: string) => string;
    activeSort: string;
    activeDirection: 'asc' | 'desc';
};

/**
 * Ordenación y `per_page` para listados Inertia + `Tabledata`: enlaces de sort, visit parcial
 * y dirección activa. El módulo aporta `indexUrl` (ruta del índice) y `filtersToQuery`.
 */
export function useTabledataListInertia<TFilters extends PaginatedListFilters>({
    appliedFilters,
    filtersToQuery,
    indexUrl,
    inertiaOnly = TABLEDATA_LIST_INERTIA_ONLY,
    perPageStorageKey,
}: UseTabledataListInertiaParams<TFilters>): TabledataListInertiaNavigation {
    const only = useMemo(() => [...inertiaOnly], [inertiaOnly]);

    const visitIndex = useCallback(
        (query: TabledataListQueryValues, options?: TabledataListVisitOptions) => {
            router.get(indexUrl(query), {}, {
                preserveState: true,
                replace: options?.replace === true,
                only,
            });
        },
        [indexUrl, only],
    );

    const applyPerPage = useCallback(
        (nextPerPage: string) => {
            const n = Number.parseInt(nextPerPage, 10);

            if (!isTabledataPerPageOption(n)) {
                return;
            }

            if (perPageStorageKey) {
                writeTabledataStoredPerPage(perPageStorageKey, n);
            }

            visitIndex(
                filtersToQuery(appliedFilters, {
                    per_page: n,
                    page: 1,
                }),
            );
        },
        [appliedFilters, filtersToQuery, perPageStorageKey, visitIndex],
    );

    const sortLink = useCallback(
        (column: string) => {
            const nextDir =
                appliedFilters.sort === column &&
                appliedFilters.direction === 'asc'
                    ? 'desc'
                    : 'asc';

            return indexUrl(
                filtersToQuery(appliedFilters, {
                    sort: column,
                    direction: nextDir,
                    page: 1,
                }),
            );
        },
        [appliedFilters, filtersToQuery, indexUrl],
    );

    const activeDirection: 'asc' | 'desc' =
        appliedFilters.direction === 'desc' ? 'desc' : 'asc';

    return {
        visitIndex,
        applyPerPage,
        sortLink,
        activeSort: appliedFilters.sort,
        activeDirection,
    };
}

// ---------------------------------------------------------------------------
// Página de listado (usePage + borrador + debounce de búsqueda)
// ---------------------------------------------------------------------------

export type TabledataListPageProps<TRow, TFilters extends PaginatedListFilters> = {
    data: Paginated<TRow>;
    filters: TFilters;
};

/** Campos de borrador que aporta tabledata a todo listado (`search`, `per_page`). */
export type TabledataListStandardDraft = {
    search: string;
    per_page: string;
};

export type TabledataMergePanelFilters<
    TFilters extends PaginatedListFilters,
    TDraft extends Record<string, unknown> & TabledataListStandardDraft,
> = (applied: TFilters, draft: TDraft) => TFilters;

export type UseTabledataListPageParams<
    TRow,
    TFilters extends PaginatedListFilters,
    TDraft extends Record<string, unknown> & TabledataListStandardDraft,
> = {
    page: TabledataListPageProps<TRow, TFilters>;
    draftFromApplied: (applied: TFilters) => TDraft;
    mergePanelFilters: TabledataMergePanelFilters<TFilters, TDraft>;
    filtersToQuery: TabledataFiltersToQuery<TFilters>;
    indexUrl: TabledataIndexUrlBuilder;
    inertiaOnly?: readonly string[];
    getSearchDraftValue: (draft: TDraft) => string;
    getSearchAppliedValue: (applied: TFilters) => string | null | undefined;
    /** Debounce del buscador; `false` o `0` lo desactiva. Por defecto 400. */
    searchDebounceMs?: number | false;
    /** Se fusiona con `TABLEDATA_LIST_RESET_QUERY` al resetear el listado. */
    moduleResetQuery?: Record<string, string | number>;
    /**
     * Clave para persistir `per_page` en `localStorage` (típica: `listConfig.storageKey` del módulo).
     */
    perPageStorageKey?: string;
};

export type TabledataListPageViewModel<
    TRow,
    TDraft extends Record<string, unknown> & TabledataListStandardDraft,
> = {
    data: Paginated<TRow>;
    filters: TDraft;
    setFilter: <K extends keyof TDraft>(key: K, value: TDraft[K]) => void;
    applyFilters: () => void;
    resetFilters: () => void;
    sort: string;
    direction: 'asc' | 'desc';
    sortLink: (column: string) => string;
    applyPerPage: (nextPerPage: string) => void;
};

/**
 * Patrón completo de índice Inertia + tabla: `usePage` (`data` + filtros aplicados), borrador local,
 * sincronía con el servidor, debounce de `search`, visitas vía `useTabledataListInertia`.
 */
export function useTabledataListPage<
    TRow,
    TFilters extends PaginatedListFilters,
    TDraft extends Record<string, unknown> & TabledataListStandardDraft,
>({
    page,
    draftFromApplied,
    mergePanelFilters,
    filtersToQuery,
    indexUrl,
    inertiaOnly,
    getSearchDraftValue,
    getSearchAppliedValue,
    searchDebounceMs = 400,
    moduleResetQuery = {},
    perPageStorageKey,
}: UseTabledataListPageParams<TRow, TFilters, TDraft>): TabledataListPageViewModel<TRow, TDraft> {
    const { data, filters: appliedFilters } = page;

    const {
        visitIndex,
        applyPerPage,
        sortLink,
        activeSort,
        activeDirection,
    } = useTabledataListInertia<TFilters>({
        appliedFilters,
        filtersToQuery,
        indexUrl,
        inertiaOnly,
        perPageStorageKey,
    });

    const appliedFiltersRef = useRef(appliedFilters);

    useEffect(() => {
        appliedFiltersRef.current = appliedFilters;
    }, [appliedFilters]);

    const perPageHydratedRef = useRef(false);

    useEffect(() => {
        if (!perPageStorageKey || perPageHydratedRef.current) {
            return;
        }

        const params = new URLSearchParams(window.location.search);

        if (params.has('per_page')) {
            writeTabledataStoredPerPage(
                perPageStorageKey,
                appliedFilters.per_page,
            );
            perPageHydratedRef.current = true;

            return;
        }

        const stored = readTabledataStoredPerPage(perPageStorageKey);

        if (stored !== null && stored !== appliedFilters.per_page) {
            perPageHydratedRef.current = true;
            visitIndex(
                filtersToQuery(appliedFilters, {
                    per_page: stored,
                    page: 1,
                }),
            );

            return;
        }

        perPageHydratedRef.current = true;
    }, [
        appliedFilters,
        appliedFilters.per_page,
        filtersToQuery,
        perPageStorageKey,
        visitIndex,
    ]);

    const [filters, setFilters] = useState<TDraft>(() => draftFromApplied(appliedFilters));
    const filtersRef = useRef(filters);

    useEffect(() => {
        filtersRef.current = filters;
    }, [filters]);

    useEffect(() => {
        startTransition(() => {
            setFilters(draftFromApplied(appliedFilters));
        });
    }, [appliedFilters, draftFromApplied]);

    const draftSearch = getSearchDraftValue(filters);

    useEffect(() => {
        if (searchDebounceMs === false || searchDebounceMs <= 0) {
            return;
        }

        const id = window.setTimeout(() => {
            const server = appliedFiltersRef.current;
            const trimmed = getSearchDraftValue(filtersRef.current).trim();
            const serverSearch = (getSearchAppliedValue(server) ?? '').trim();

            if (trimmed === serverSearch) {
                return;
            }

            visitIndex(
                filtersToQuery(server, {
                    search: trimmed || undefined,
                    page: 1,
                }),
                { replace: true },
            );
        }, searchDebounceMs);

        return () => window.clearTimeout(id);
    }, [
        draftSearch,
        filtersToQuery,
        getSearchAppliedValue,
        getSearchDraftValue,
        searchDebounceMs,
        visitIndex,
    ]);

    const panelFilters = useMemo(
        () => mergePanelFilters(appliedFilters, filters),
        [appliedFilters, filters, mergePanelFilters],
    );

    const applyFilters = useCallback(() => {
        visitIndex({
            ...filtersToQuery(panelFilters),
            page: 1,
        });
    }, [filtersToQuery, panelFilters, visitIndex]);

    const resetFilters = useCallback(() => {
        if (perPageStorageKey) {
            writeTabledataStoredPerPageDefault(perPageStorageKey);
        }

        visitIndex({
            ...TABLEDATA_LIST_RESET_QUERY,
            ...moduleResetQuery,
        });
    }, [moduleResetQuery, perPageStorageKey, visitIndex]);

    const setFilter = useCallback(
        <K extends keyof TDraft>(key: K, value: TDraft[K]) => {
            setFilters((prev) => ({ ...prev, [key]: value }));
        },
        [],
    );

    return {
        data,
        filters,
        setFilter,
        applyFilters,
        resetFilters,
        sort: activeSort,
        direction: activeDirection,
        sortLink,
        applyPerPage,
    };
}

type ListInertiaWithoutPage<
    TRow,
    TFilters extends PaginatedListFilters,
    TDraft extends Record<string, unknown> & TabledataListStandardDraft,
> = Omit<UseTabledataListPageParams<TRow, TFilters, TDraft>, 'page'>;

/**
 * Construye los parámetros de `useTabledataListPage` a partir del borrador **solo del módulo**
 * (`draftFromModule` sin `search` ni `per_page`) y opcionalmente getters de búsqueda.
 */
export function buildTabledataListInertiaParams<
    TRow,
    TFilters extends PaginatedListFilters,
    TDraftModule extends Record<string, unknown>,
>(
    args: Omit<
        ListInertiaWithoutPage<
            TRow,
            TFilters,
            TDraftModule & TabledataListStandardDraft
        >,
        'draftFromApplied' | 'getSearchDraftValue' | 'getSearchAppliedValue'
    > & {
        draftFromModule: (applied: TFilters) => TDraftModule;
        getSearchDraftValue?: (
            draft: TDraftModule & TabledataListStandardDraft,
        ) => string;
        getSearchAppliedValue?: (
            applied: TFilters,
        ) => string | null | undefined;
    },
): ListInertiaWithoutPage<
    TRow,
    TFilters,
    TDraftModule & TabledataListStandardDraft
> {
    const {
        draftFromModule,
        getSearchDraftValue = (d) => d.search,
        getSearchAppliedValue = (a) => a.search,
        ...rest
    } = args;

    const draftFromApplied = (
        applied: TFilters,
    ): TDraftModule & TabledataListStandardDraft => ({
        ...draftFromModule(applied),
        search: (applied.search ?? '') as string,
        per_page: String(applied.per_page),
    });

    return {
        ...rest,
        draftFromApplied,
        getSearchDraftValue,
        getSearchAppliedValue,
    };
}

type ModuleStringDraft<TKeys extends readonly string[]> = {
    [K in TKeys[number]]: string;
};

/**
 * Listados cuyo módulo solo añade **strings** en query: misma lista de claves para borrador, merge y `filtersToQuery`.
 * Evita repetir `draftFromModule` / `mergePanelFilters` / `filtersToQuery` en cada página.
 */
export function buildTabledataListInertiaForModuleStringKeys<
    TRow,
    TFilters extends PaginatedListFilters,
    const TKeys extends readonly string[],
>(args: {
    moduleKeys: TKeys;
    indexUrl: TabledataIndexUrlBuilder;
    moduleResetQuery?: Record<string, string | number>;
    inertiaOnly?: readonly string[];
    searchDebounceMs?: number | false;
}): ListInertiaWithoutPage<
    TRow,
    TFilters,
    ModuleStringDraft<TKeys> & TabledataListStandardDraft
> {
    const {
        moduleKeys,
        indexUrl,
        moduleResetQuery,
        inertiaOnly,
        searchDebounceMs,
    } = args;

    return buildTabledataListInertiaParams<
        TRow,
        TFilters,
        ModuleStringDraft<TKeys>
    >({
        draftFromModule: (applied) =>
            tabledataModuleDraftFromApplied(moduleKeys, applied),
        mergePanelFilters: (applied, draft) =>
            tabledataMergeModuleFilterDraft(moduleKeys, applied, draft),
        filtersToQuery: (filters, overrides) =>
            tabledataListFiltersToQueryWithModuleKeys(
                moduleKeys,
                filters,
                overrides,
            ),
        indexUrl,
        moduleResetQuery,
        inertiaOnly,
        searchDebounceMs,
    });
}
