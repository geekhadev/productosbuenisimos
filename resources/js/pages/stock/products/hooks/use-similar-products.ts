import { useCallback, useRef, useState } from 'react';
import {
    search as searchAction,
    store as storeAction,
    destroy as destroyAction,
} from '@/actions/App/Http/Controllers/Stock/ProductSimilarProductsController';
import { jsonFetchHeaders } from '@/lib/json-fetch-headers';
import type { SimilarProductItem } from '@/pages/stock/products/types';

type SimilarApiResponse = {
    similar: SimilarProductItem[];
};

type SearchApiResponse = {
    results: SimilarProductItem[];
};

async function fetchJson<T>(url: string, options: RequestInit): Promise<T> {
    const response = await fetch(url, {
        ...options,
        headers: { ...jsonFetchHeaders(), ...(options.headers ?? {}) },
        credentials: 'same-origin',
    });

    const payload = (await response.json()) as T & { message?: string; errors?: Record<string, string[]> };

    if (!response.ok) {
        const firstFieldError = (payload as { errors?: Record<string, string[]> }).errors
            ? Object.values((payload as { errors: Record<string, string[]> }).errors).flat()[0]
            : undefined;
        throw new Error(firstFieldError ?? (payload as { message?: string }).message ?? 'No se pudo completar la operación.');
    }

    return payload;
}

export function useSimilarProducts(productId: string, initial: SimilarProductItem[]) {
    const [similar, setSimilar] = useState<SimilarProductItem[]>(initial);
    const [error, setError] = useState<string | null>(null);
    const [searchResults, setSearchResults] = useState<SimilarProductItem[]>([]);
    const [searching, setSearching] = useState(false);
    const [saving, setSaving] = useState(false);
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const searchProducts = useCallback((query: string) => {
        if (debounceRef.current) clearTimeout(debounceRef.current);

        if (query.trim() === '') {
            setSearchResults([]);
            return;
        }

        debounceRef.current = setTimeout(() => {
            setSearching(true);
            const url = searchAction.url(productId, { query: { q: query } });

            fetchJson<SearchApiResponse>(url, { method: 'GET' })
                .then((payload) => setSearchResults(payload.results))
                .catch((err: unknown) => {
                    setSearchResults([]);
                    setError(err instanceof Error ? err.message : 'Error en la búsqueda.');
                })
                .finally(() => setSearching(false));
        }, 300);
    }, [productId]);

    const attachProduct = useCallback(async (similarProductId: string) => {
        setError(null);
        setSaving(true);

        try {
            const payload = await fetchJson<SimilarApiResponse>(storeAction.url(productId), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ similar_product_id: similarProductId }),
            });
            setSimilar(payload.similar);
            setSearchResults([]);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'No se pudo agregar el producto similar.');
        } finally {
            setSaving(false);
        }
    }, [productId]);

    const detachProduct = useCallback(async (similarProductId: string) => {
        setError(null);

        try {
            const payload = await fetchJson<SimilarApiResponse>(
                destroyAction.url({ product: productId, similarProduct: similarProductId }),
                { method: 'DELETE' },
            );
            setSimilar(payload.similar);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'No se pudo quitar el producto similar.');
        }
    }, [productId]);

    return {
        similar,
        error,
        searchResults,
        searching,
        saving,
        searchProducts,
        attachProduct,
        detachProduct,
        clearSearch: () => setSearchResults([]),
    };
}
