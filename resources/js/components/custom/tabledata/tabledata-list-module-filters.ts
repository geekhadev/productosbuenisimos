import { tabledataListFiltersToQuery } from '@/components/custom/tabledata/tabledata-list-query';
import type { TabledataListStandardDraft } from '@/components/custom/tabledata/use-tabledata-list-inertia';
import type { PaginatedListFilters } from '@/types/list-filters';

/**
 * Borrador **solo del módulo**: copia cada clave de `moduleKeys` desde filtros aplicados a `string`
 * (`''` si el valor es `null` / `undefined`).
 */
export function tabledataModuleDraftFromApplied<
    const TKeys extends readonly string[],
    TApplied extends PaginatedListFilters,
>(moduleKeys: TKeys, applied: TApplied): { [K in TKeys[number]]: string } {
    const appliedRecord = applied as Record<string, unknown>;

    return Object.fromEntries(
        moduleKeys.map((k) => [
            k,
            appliedRecord[k] == null ? '' : String(appliedRecord[k]),
        ]),
    ) as { [K in TKeys[number]]: string };
}

/**
 * Fusiona filtros aplicados con el borrador completo (campos del módulo + `search` / `per_page` de tabledata).
 */
export function tabledataMergeModuleFilterDraft<
    const TKeys extends readonly string[],
    TFilters extends PaginatedListFilters,
>(
    moduleKeys: TKeys,
    applied: TFilters,
    draft: TabledataListStandardDraft & { [K in TKeys[number]]: string },
): TFilters {
    const fromModule: Partial<Record<TKeys[number], string | undefined>> = {};

    for (const k of moduleKeys) {
        const v = draft[k as TKeys[number]].trim();
        fromModule[k as TKeys[number]] = v || undefined;
    }

    return {
        sort: applied.sort,
        direction: applied.direction,
        per_page:
            Number.parseInt(draft.per_page, 10) || applied.per_page,
        search: draft.search.trim() || undefined,
        ...fromModule,
    } as TFilters;
}

/**
 * Serializa filtros de listado: campos estándar + las claves de módulo indicadas (mismas que en borrador/merge).
 */
export function tabledataListFiltersToQueryWithModuleKeys<
    const TKeys extends readonly string[],
    TFilters extends PaginatedListFilters,
>(
    moduleKeys: TKeys,
    filters: TFilters,
    overrides: Record<string, unknown> = {},
) {
    const customStringFields: Record<string, string | null | undefined> = {};

    for (const k of moduleKeys) {
        customStringFields[k] = filters[k as keyof TFilters] as
            | string
            | null
            | undefined;
    }

    return tabledataListFiltersToQuery(filters, customStringFields, overrides);
}
