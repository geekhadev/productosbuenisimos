import {
    TABLEDATA_DEFAULT_PER_PAGE,
    TABLEDATA_PER_PAGE_STORAGE_PREFIX,
    isTabledataPerPageOption,
} from '@/components/custom/tabledata/config';

export function tabledataPerPageStorageKey(storageKey: string): string {
    return `${TABLEDATA_PER_PAGE_STORAGE_PREFIX}${storageKey}`;
}

/**
 * Lee un `per_page` persistido para este listado, o `null` si no hay valor válido.
 */
export function readTabledataStoredPerPage(storageKey: string): number | null {
    if (typeof window === 'undefined') {
        return null;
    }

    try {
        const raw = window.localStorage.getItem(
            tabledataPerPageStorageKey(storageKey),
        );

        if (raw === null || raw === '') {
            return null;
        }

        const n = Number.parseInt(raw, 10);

        if (!Number.isFinite(n) || !isTabledataPerPageOption(n)) {
            return null;
        }

        return n;
    } catch {
        return null;
    }
}

/**
 * Persiste el `per_page` elegido en el selector del listado.
 */
export function writeTabledataStoredPerPage(
    storageKey: string,
    perPage: number,
): void {
    if (typeof window === 'undefined') {
        return;
    }

    if (!isTabledataPerPageOption(perPage)) {
        return;
    }

    try {
        window.localStorage.setItem(
            tabledataPerPageStorageKey(storageKey),
            String(perPage),
        );
    } catch {
        // Quota u otro error: no bloquear la UI.
    }
}

/**
 * Persiste el valor por defecto de listados (p. ej. tras “reiniciar filtros”).
 */
export function writeTabledataStoredPerPageDefault(storageKey: string): void {
    writeTabledataStoredPerPage(storageKey, TABLEDATA_DEFAULT_PER_PAGE);
}
