import { TABLEDATA_HIDDEN_COLUMNS_STORAGE_PREFIX } from '@/components/custom/tabledata/config';

export function tabledataHiddenColumnsStorageKey(storageKey: string): string {
    return `${TABLEDATA_HIDDEN_COLUMNS_STORAGE_PREFIX}${storageKey}`;
}

/**
 * Lee las claves de columnas ocultas persistidas para esta tabla.
 */
export function readTabledataHiddenColumnKeys(storageKey: string): Set<string> {
    if (typeof window === 'undefined') {
        return new Set();
    }

    try {
        const raw = window.localStorage.getItem(
            tabledataHiddenColumnsStorageKey(storageKey),
        );

        if (raw === null || raw === '') {
            return new Set();
        }

        const parsed = JSON.parse(raw) as unknown;

        if (!Array.isArray(parsed)) {
            return new Set();
        }

        return new Set(
            parsed.filter((k): k is string => typeof k === 'string' && k !== ''),
        );
    } catch {
        return new Set();
    }
}

/**
 * Persiste las columnas ocultas para la clave de tabla dada.
 */
export function writeTabledataHiddenColumnKeys(
    storageKey: string,
    keys: Set<string>,
): void {
    if (typeof window === 'undefined') {
        return;
    }

    try {
        window.localStorage.setItem(
            tabledataHiddenColumnsStorageKey(storageKey),
            JSON.stringify([...keys].sort()),
        );
    } catch {
        // Quota u otro error: no bloquear la UI.
    }
}

function sameStringSet(a: Set<string>, b: Set<string>): boolean {
    if (a.size !== b.size) {
        return false;
    }

    for (const k of a) {
        if (!b.has(k)) {
            return false;
        }
    }

    return true;
}

/**
 * Devuelve `next` si difiere de `prev`; si no, `prev` (evita re-renders).
 */
export function mergePrunedHiddenKeys(
    prev: Set<string>,
    next: Set<string>,
): Set<string> {
    return sameStringSet(prev, next) ? prev : next;
}
