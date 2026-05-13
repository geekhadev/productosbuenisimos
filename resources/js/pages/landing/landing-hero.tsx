import { Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { formatLandingProductPrice } from '@/pages/landing/format-price';
import { landingProductImageAreaClass } from '@/pages/landing/landing-product-visual';
import type { LandingProduct, LandingSectionProps } from '@/pages/landing/types';
import { dashboard, login } from '@/routes';

const PRODUCT_PLACEHOLDER = '/product-placeholder.svg';

type LandingHeroProps = LandingSectionProps & {
    heroProduct: LandingProduct | null;
};

export function LandingHero({ canRegister, heroProduct }: LandingHeroProps) {
    const { auth } = usePage().props;
    const isLoggedIn = Boolean(auth.user);

    return (
        <section
            id="inicio"
            className="relative scroll-mt-20 border-b border-border/60 bg-linear-to-b from-muted/40 via-background to-background"
        >
            <div className="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
                {heroProduct === null ? (
                    <div className="flex min-h-64 items-center justify-center text-center">
                        <p className="text-muted-foreground">
                            Pronto encontrarás nuestros productos aquí.
                        </p>
                    </div>
                ) : (
                    <div className="grid min-h-[min(22rem,65svh)] items-center gap-10 sm:gap-12 lg:min-h-104 lg:grid-cols-2 lg:gap-x-12 lg:gap-y-14">
                        <div className="order-2 flex flex-col justify-center lg:order-1">
                            <p className="mb-3 text-sm font-medium uppercase tracking-wide text-primary">
                                Tienda online
                            </p>
                            <h1 className="text-3xl font-semibold tracking-tight text-foreground sm:text-4xl lg:text-5xl lg:leading-[1.1] xl:text-6xl">
                                {heroProduct.name}
                            </h1>
                            {heroProduct.description && (
                                <p className="mt-4 line-clamp-3 max-w-xl text-base text-muted-foreground sm:text-lg">
                                    {heroProduct.description}
                                </p>
                            )}
                            <p className="mt-6 text-4xl font-bold tracking-tight text-primary sm:text-5xl lg:text-6xl">
                                {formatLandingProductPrice(heroProduct.price)}
                            </p>
                            <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                                <Button size="lg" type="button" variant="default">
                                    Ver detalles
                                </Button>

                            </div>
                        </div>
                        <div className="relative order-1 mx-auto w-full max-w-xl lg:order-2 lg:max-w-none">
                            <div
                                className={cn(
                                    landingProductImageAreaClass,
                                    'rounded-2xl border border-border/60 shadow-lg',
                                )}
                            >
                                <img
                                    src={PRODUCT_PLACEHOLDER}
                                    alt={heroProduct.name}
                                    className="size-full object-cover"
                                    loading="eager"
                                    decoding="async"
                                />
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </section>
    );
}
