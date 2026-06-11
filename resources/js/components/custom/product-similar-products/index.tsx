import { Loader2, Plus, Search, X } from 'lucide-react';
import { useRef, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import type { SimilarProductItem } from '@/pages/stock/products/types';

type Props = {
    similar: SimilarProductItem[];
    editable: boolean;
    searchResults: SimilarProductItem[];
    searching: boolean;
    saving: boolean;
    onSearch: (query: string) => void;
    onAttach: (id: string) => void;
    onDetach: (id: string) => void;
    onClearSearch: () => void;
};

export function ProductSimilarProducts({
    similar,
    editable,
    searchResults,
    searching,
    saving,
    onSearch,
    onAttach,
    onDetach,
    onClearSearch,
}: Props) {
    const [query, setQuery] = useState('');
    const [open, setOpen] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);

    function handleQueryChange(value: string) {
        setQuery(value);
        setOpen(value.trim() !== '');
        onSearch(value);
    }

    function handleAttach(item: SimilarProductItem) {
        onAttach(item.id);
        setQuery('');
        setOpen(false);
        onClearSearch();
    }

    function handleBlur() {
        setTimeout(() => {
            setOpen(false);
            onClearSearch();
        }, 150);
    }

    return (
        <div className="space-y-3">
            {editable && (
                <div className="relative">
                    <div className="relative flex items-center">
                        <Search className="text-muted-foreground absolute left-3 h-4 w-4" />
                        <Input
                            ref={inputRef}
                            placeholder="Buscar por nombre, código o SKU…"
                            className="pl-9 pr-4"
                            value={query}
                            onChange={(e) => handleQueryChange(e.target.value)}
                            onFocus={() => query.trim() !== '' && setOpen(true)}
                            onBlur={handleBlur}
                            disabled={saving}
                        />
                        {searching && (
                            <Loader2 className="text-muted-foreground absolute right-3 h-4 w-4 animate-spin" />
                        )}
                    </div>

                    {open && (
                        <div className="bg-popover border-border absolute z-10 mt-1 w-full rounded-md border shadow-md">
                            {searchResults.length === 0 && !searching ? (
                                <p className="text-muted-foreground px-3 py-2 text-sm">
                                    No se encontraron productos.
                                </p>
                            ) : (
                                <ul>
                                    {searchResults.map((item) => (
                                        <li key={item.id}>
                                            <button
                                                type="button"
                                                className={cn(
                                                    'hover:bg-accent flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm',
                                                    'first:rounded-t-md last:rounded-b-md',
                                                )}
                                                onMouseDown={(e) => e.preventDefault()}
                                                onClick={() => handleAttach(item)}
                                            >
                                                <span className="min-w-0 truncate font-medium">
                                                    {item.name}
                                                </span>
                                                <span className="text-muted-foreground shrink-0 text-xs">
                                                    {item.code}
                                                </span>
                                                <Plus className="text-muted-foreground h-4 w-4 shrink-0" />
                                            </button>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    )}
                </div>
            )}

            {similar.length === 0 ? (
                <p className="text-muted-foreground text-sm italic">
                    No hay productos similares asociados.
                </p>
            ) : (
                <ul className="divide-border divide-y rounded-md border">
                    {similar.map((item) => (
                        <li
                            key={item.id}
                            className="flex items-center justify-between gap-2 px-3 py-2"
                        >
                            <div className="min-w-0">
                                <span className="block truncate text-sm font-medium">
                                    {item.name}
                                </span>
                                <span className="text-muted-foreground text-xs">
                                    {item.code} · {item.sku}
                                </span>
                            </div>
                            {editable && (
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    className="h-7 w-7 shrink-0"
                                    onClick={() => onDetach(item.id)}
                                    title="Quitar producto similar"
                                >
                                    <X className="h-4 w-4" />
                                </Button>
                            )}
                        </li>
                    ))}
                </ul>
            )}

            {saving && (
                <div className="text-muted-foreground flex items-center gap-2 text-sm">
                    <Loader2 className="h-4 w-4 animate-spin" />
                    Guardando…
                </div>
            )}
        </div>
    );
}
