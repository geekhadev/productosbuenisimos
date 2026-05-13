import { cn } from '@/lib/utils';
import type { LandingShowcaseProduct } from '@/pages/landing/types';

type LandingProductCardProps = {
    product: LandingShowcaseProduct;
    variant: 'hero' | 'grid';
};

export function LandingProductCard({ product, variant }: LandingProductCardProps) {
    const isHero = variant === 'hero';

    return (
        <article
            className={cn(
                'group flex flex-col overflow-hidden rounded-2xl border border-border/80 bg-card shadow-xs transition-shadow hover:shadow-md',
                isHero ? 'lg:max-w-none' : '',
            )}
        >
            <div
                className={cn(
                    'relative overflow-hidden bg-muted',
                    isHero ? 'aspect-4/3 sm:aspect-5/4' : 'aspect-square sm:aspect-5/4',
                )}
            >
                <img
                    src={product.imageUrl}
                    alt={product.name}
                    className="size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                    loading={isHero ? 'eager' : 'lazy'}
                    decoding="async"
                />
                {product.badge ? (
                    <span className="absolute left-3 top-3 rounded-full bg-primary px-3 py-1 text-xs font-medium text-primary-foreground shadow-sm">
                        {product.badge}
                    </span>
                ) : null}
            </div>
            <div className={cn('flex flex-1 flex-col', isHero ? 'p-5 sm:p-6' : 'p-4')}>
                <h3
                    className={cn(
                        'font-semibold tracking-tight text-foreground',
                        isHero ? 'text-lg sm:text-xl' : 'text-base',
                    )}
                >
                    {product.name}
                </h3>
                <p
                    className={cn(
                        'mt-2 flex-1 text-muted-foreground',
                        isHero ? 'text-sm sm:text-base' : 'text-sm leading-relaxed',
                    )}
                >
                    {product.subtitle}
                </p>
                <p
                    className={cn(
                        'mt-4 font-semibold text-foreground',
                        isHero ? 'text-xl' : 'text-lg',
                    )}
                >
                    {product.priceLabel}
                </p>
                <a
                    href="#destacados"
                    className={cn(
                        'mt-4 inline-flex w-full items-center justify-center rounded-md border border-border bg-background px-4 py-2.5 text-center text-sm font-medium text-foreground transition-colors hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-card',
                        isHero ? 'sm:py-3' : '',
                    )}
                >
                    Ver más en destacados
                </a>
            </div>
        </article>
    );
}
