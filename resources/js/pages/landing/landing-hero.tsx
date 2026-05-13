import { Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { landingHeroProduct } from '@/pages/landing/landing-showcase-data';
import type { LandingSectionProps } from '@/pages/landing/types';
import { dashboard, login } from '@/routes';

export function LandingHero({ canRegister }: LandingSectionProps) {
    const { auth } = usePage().props;
    const product = landingHeroProduct;
    const isLoggedIn = Boolean(auth.user);

    return (
        <section
            id="inicio"
            className="relative scroll-mt-20 border-b border-border/60 bg-linear-to-b from-muted/40 via-background to-background"
        >
            <div className="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
                <div className="grid min-h-[min(22rem,65svh)] items-center gap-10 sm:gap-12 lg:min-h-104 lg:grid-cols-2 lg:gap-x-12 lg:gap-y-14">
                    <div className="order-2 flex flex-col justify-center lg:order-1">
                        <p className="mb-3 text-sm font-medium uppercase tracking-wide text-primary">
                            Tienda online
                        </p>
                        {product.badge ? (
                            <span className="mb-3 inline-flex w-fit rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                                {product.badge}
                            </span>
                        ) : null}
                        <h1 className="text-3xl font-semibold tracking-tight text-foreground sm:text-4xl lg:text-5xl lg:leading-[1.1]">
                            {product.name}
                        </h1>
                        <p className="mt-4 max-w-xl text-base text-muted-foreground sm:text-lg">
                            {product.subtitle}
                        </p>
                        <p className="mt-6 text-3xl font-semibold tracking-tight text-foreground sm:text-4xl">
                            {product.priceLabel}
                        </p>
                        <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                            {isLoggedIn ? (
                                <Button size="lg" asChild>
                                    <Link href={dashboard()}>Mi cuenta</Link>
                                </Button>
                            ) : canRegister ? (
                                <Button size="lg" asChild>
                                    <Link href="/register">Crear cuenta</Link>
                                </Button>
                            ) : (
                                <Button size="lg" asChild>
                                    <Link href={login()}>Iniciar sesión</Link>
                                </Button>
                            )}
                            <Button size="lg" variant="outline" asChild>
                                <a href="#destacados">Ver más productos</a>
                            </Button>
                        </div>
                    </div>
                    <div className="relative order-1 mx-auto w-full max-w-xl lg:order-2 lg:max-w-none">
                        <div className="relative aspect-4/3 overflow-hidden rounded-2xl border border-border/60 bg-muted shadow-lg sm:aspect-5/4 lg:aspect-square">
                            <img
                                src={product.imageUrl}
                                alt={product.name}
                                className="size-full object-cover"
                                loading="eager"
                                decoding="async"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
