import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { formatLandingProductPrice } from '@/pages/landing/format-price';
import { landingProductImageAreaClass } from '@/pages/landing/landing-product-visual';
import type { LandingProduct } from '@/pages/landing/types';
import { show as landingProductShow } from '@/routes/landing/products';

const PRODUCT_PLACEHOLDER = '/product-placeholder.svg';

type LandingProductCardProps = {
    product: LandingProduct;
    variant: 'hero' | 'grid';
};

export function LandingProductCard({ product, variant }: LandingProductCardProps) {
    const isHero = variant === 'hero';

    return (
        <article
            className={cn(
                'group flex h-full min-w-0 flex-col overflow-hidden rounded-2xl border border-border/80 bg-card shadow-xs transition-shadow hover:shadow-md',
                isHero ? 'lg:max-w-none' : 'w-full',
            )}
        >
            <Link
                className="block shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background"
                href={landingProductShow.url(product.id)}
            >
                <div className={cn(landingProductImageAreaClass, 'rounded-t-2xl')}>
                    <img
                        src={PRODUCT_PLACEHOLDER}
                        alt={product.name}
                        className="size-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                        loading={isHero ? 'eager' : 'lazy'}
                        decoding="async"
                    />
                </div>
            </Link>
            <div className={cn('flex flex-1 flex-col', isHero ? 'p-5 sm:p-6' : 'p-4 sm:p-5')}>
                <h3
                    className={cn(
                        'font-semibold tracking-tight text-foreground',
                        isHero
                            ? 'text-2xl sm:text-3xl lg:text-4xl'
                            : 'text-2xl sm:text-3xl',
                    )}
                >
                    <Link
                        className="rounded-sm underline-offset-4 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background"
                        href={landingProductShow.url(product.id)}
                    >
                        {product.name}
                    </Link>
                </h3>
                {product.description && (
                    <p
                        className={cn(
                            'mt-2 flex-1 text-muted-foreground',
                            isHero
                                ? 'line-clamp-3 text-sm sm:text-base'
                                : 'line-clamp-2 text-sm leading-relaxed sm:text-base',
                        )}
                    >
                        {product.description}
                    </p>
                )}
                <p
                    className={cn(
                        'mt-auto pt-4 font-bold tracking-tight text-primary',
                        isHero ? 'text-3xl sm:text-4xl' : 'text-2xl sm:text-3xl lg:text-4xl',
                    )}
                >
                    {formatLandingProductPrice(product.price)}
                </p>
                <Button asChild className="mt-4 w-full" size="lg" variant="default">
                    <Link href={landingProductShow.url(product.id)}>Ver detalles</Link>
                </Button>
            </div>
        </article>
    );
}
