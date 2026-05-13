import type { TabledataColumn } from '@/components/custom/tabledata/tabledata.types';

export function isColumnHideable<T>(col: TabledataColumn<T>): boolean {
    return col.hideable !== false;
}

export function tabledataPruneHiddenKeys(
    columns: { key: string }[],
    hiddenKeys: Set<string>,
): Set<string> {
    const valid = new Set(columns.map((c) => c.key));

    return new Set([...hiddenKeys].filter((k) => valid.has(k)));
}

export function tabledataApplyColumnHidden<T>(
    columns: TabledataColumn<T>[],
    prev: Set<string>,
    key: string,
    hidden: boolean,
): Set<string> {
    const col = columns.find((c) => c.key === key);

    if (!col || !isColumnHideable(col)) {
        return prev;
    }

    const next = new Set(prev);

    if (hidden) {
        next.add(key);
        const visibleAfter = columns.filter(
            (c) => !isColumnHideable(c) || !next.has(c.key),
        );

        if (visibleAfter.length < 1) {
            return prev;
        }

        return next;
    }

    next.delete(key);

    return next;
}
