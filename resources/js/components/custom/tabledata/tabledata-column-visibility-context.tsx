import { createContext, useContext, useEffect, useLayoutEffect, useMemo, useState } from 'react';
import type { Dispatch, ReactNode, SetStateAction } from 'react';
import { tabledataPruneHiddenKeys } from '@/components/custom/tabledata/tabledata-column-visibility';
import {
    readTabledataHiddenColumnKeys,
    writeTabledataHiddenColumnKeys,
} from '@/components/custom/tabledata/tabledata-hidden-columns-storage';
import type { TabledataColumn } from '@/components/custom/tabledata/tabledata.types';
import { cn } from '@/lib/utils';

export type TabledataColumnVisibilityContextValue<T = unknown> = {
    storageKey: string;
    columns: TabledataColumn<T>[];
    hiddenKeys: Set<string>;
    setHiddenKeys: Dispatch<SetStateAction<Set<string>>>;
};

const TabledataColumnVisibilityContext = createContext<
    TabledataColumnVisibilityContextValue | null
>(null);

export function useTabledataColumnVisibilityOptional(): TabledataColumnVisibilityContextValue | null {
    return useContext(TabledataColumnVisibilityContext);
}

export type TabledataColumnVisibilityProviderProps<T> = {
    /** Identificador único de esta tabla (clave en `localStorage`). */
    storageKey: string;
    columns: TabledataColumn<T>[];
    children: ReactNode;
};

/**
 * Provee estado de columnas ocultas persistido en `localStorage` para `storageKey`.
 * Envuelve la página o bloque donde uses `Tabledata` y `TabledataColumnPicker`
 * sin pasar `hiddenKeys` manualmente.
 *
 * Con Inertia SSR, el primer render del servidor no puede leer `localStorage`;
 * se hidrata con “ninguna oculta” y en `useLayoutEffect` (antes del pintado del cliente)
 * se aplica lo guardado y se muestra el bloque, evitando el parpadeo de columnas.
 */
export function TabledataColumnVisibilityProvider<T>({
    storageKey,
    columns,
    children,
}: TabledataColumnVisibilityProviderProps<T>) {
    const [hiddenKeys, setHiddenKeys] = useState<Set<string>>(() => new Set());
    const [layoutReady, setLayoutReady] = useState(false);

    useLayoutEffect(() => {
        const fromStorage = readTabledataHiddenColumnKeys(storageKey);
        const pruned = tabledataPruneHiddenKeys(columns, fromStorage);

        // Sincronizar con `localStorage` tras el HTML de SSR (sin `window`) antes del
        // primer pintado del cliente; evita parpadeo de columnas.
        /* eslint-disable react-hooks/set-state-in-effect -- useLayoutEffect intencional */
        setHiddenKeys(pruned);
        setLayoutReady(true);
        /* eslint-enable react-hooks/set-state-in-effect */
    }, [storageKey, columns]);

    useEffect(() => {
        if (!layoutReady) {
            return;
        }

        writeTabledataHiddenColumnKeys(storageKey, hiddenKeys);
    }, [storageKey, hiddenKeys, layoutReady]);

    const value = useMemo<TabledataColumnVisibilityContextValue<T>>(
        () => ({
            storageKey,
            columns,
            hiddenKeys,
            setHiddenKeys,
        }),
        [storageKey, columns, hiddenKeys, setHiddenKeys],
    );

    return (
        <TabledataColumnVisibilityContext.Provider
            value={value as TabledataColumnVisibilityContextValue}
        >
            <div
                className={cn(
                    !layoutReady &&
                        'pointer-events-none opacity-0 select-none',
                    layoutReady && 'transition-opacity duration-150',
                )}
            >
                {children}
            </div>
        </TabledataColumnVisibilityContext.Provider>
    );
}
