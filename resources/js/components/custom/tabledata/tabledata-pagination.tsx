import { Link } from '@inertiajs/react';
import {
    useTabledataPagination
    
} from '@/components/custom/tabledata/tabledata-pagination-state';
import type {TabledataPaginationSlice} from '@/components/custom/tabledata/tabledata-pagination-state';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type TabledataPaginationProps = {
    paginator: TabledataPaginationSlice;
    /** Props `only` de Inertia al navegar entre páginas (ver `TABLEDATA_LIST_INERTIA_ONLY` en `config.ts`). */
    only?: string[];
    className?: string;
};

/**
 * Enlaces de página estándar del paginador de Laravel (HTML en `label`).
 * Reutilizable en cualquier listado Inertia que reciba `links` + `last_page`.
 */
export function TabledataPagination({
    paginator,
    only = [],
    className,
}: TabledataPaginationProps) {
    const { shouldRender, links } = useTabledataPagination(paginator);

    if (!shouldRender) {
        return null;
    }

    return (
        <div
            className={cn(
                'flex flex-wrap items-center justify-center gap-2',
                className,
            )}
        >
            {links.map((link, i) => {
                if (link.url === null) {
                    return (
                        <span
                            key={`${link.label}-${i}`}
                            className="px-2 py-1 text-sm text-muted-foreground"
                            dangerouslySetInnerHTML={{
                                __html: link.label,
                            }}
                        />
                    );
                }

                return (
                    <Button
                        key={`${link.label}-${i}`}
                        variant={link.active ? 'default' : 'outline'}
                        size="sm"
                        asChild
                    >
                        <Link
                            href={link.url}
                            preserveScroll
                            {...(only.length > 0 ? { only } : {})}
                        >
                            <span
                                dangerouslySetInnerHTML={{
                                    __html: link.label,
                                }}
                            />
                        </Link>
                    </Button>
                );
            })}
        </div>
    );
}
