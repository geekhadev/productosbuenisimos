import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { formatLandingDimension } from '@/pages/landing/format-dimension';
import { formatLandingProductPrice } from '@/pages/landing/format-price';
import { LandingFooter } from '@/pages/landing/landing-footer';
import { LandingHeader } from '@/pages/landing/landing-header';
import { landingProductImageAreaClass } from '@/pages/landing/landing-product-visual';
import type { PublicLandingProductDetail } from '@/pages/landing/types';
import { home } from '@/routes';

const PRODUCT_PLACEHOLDER = '/product-placeholder.svg';

function priceToNumber(price: string | number): number {
    return typeof price === 'string' ? Number.parseFloat(price) : price;
}

type ProductShowPageProps = {
    canRegister?: boolean;
    product: PublicLandingProductDetail;
};

export default function ProductShow({ canRegister = true, product }: ProductShowPageProps) {
    const priceLabel = formatLandingProductPrice(priceToNumber(product.price));

    const rows: { label: string; value: string }[] = [
        { label: 'Código', value: product.code },
        { label: 'SKU', value: product.sku },
        { label: 'Precio', value: priceLabel },
        { label: 'Stock mínimo', value: String(product.minimum_stock) },
        {
            label: 'Ancho',
            value: `${formatLandingDimension(product.width)} cm`,
        },
        {
            label: 'Largo',
            value: `${formatLandingDimension(product.length)} cm`,
        },
        {
            label: 'Alto',
            value: `${formatLandingDimension(product.height)} cm`,
        },
        {
            label: 'Volumen',
            value: `${formatLandingDimension(product.volume)} cm³`,
        },
        {
            label: 'Peso',
            value: `${formatLandingDimension(product.weight)} kg`,
        },
    ];

    return (
        <div id="top" className="min-h-svh bg-background text-foreground">
            <Head title={`${product.name} — Productos Buenísimos`} />
            <LandingHeader canRegister={canRegister} />
            <main className="border-b border-border/60">
                <div className="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
                    <Button asChild className="mb-6" size="sm" variant="ghost">
                        <Link className="gap-2" href={home.url()}>
                            <ArrowLeft aria-hidden className="size-4" />
                            Volver al inicio
                        </Link>
                    </Button>
                    <div className="grid gap-10 lg:grid-cols-2 lg:gap-12">
                        <div
                            className={cn(
                                landingProductImageAreaClass,
                                'mx-auto w-full max-w-xl overflow-hidden rounded-2xl border border-border/60 shadow-lg lg:max-w-none',
                            )}
                        >
                            <img
                                src={PRODUCT_PLACEHOLDER}
                                alt=""
                                className="size-full object-cover"
                                loading="eager"
                                decoding="async"
                            />
                        </div>
                        <div className="min-w-0">
                            <p className="text-sm font-medium uppercase tracking-wide text-primary">
                                Producto
                            </p>
                            <h1 className="mt-2 text-3xl font-semibold tracking-tight text-foreground sm:text-4xl">
                                {product.name}
                            </h1>
                            {product.description ? (
                                <p className="mt-4 text-base text-muted-foreground sm:text-lg">
                                    {product.description}
                                </p>
                            ) : null}
                            <dl className="mt-6 rounded-lg border border-border/60 bg-card/40 px-3 py-0.5">
                                {rows.map((row) => (
                                    <div
                                        key={row.label}
                                        className="flex min-h-8 items-baseline justify-between gap-3 border-b border-border/50 py-1.5 last:border-b-0"
                                    >
                                        <dt className="shrink-0 text-xs font-medium text-muted-foreground">
                                            {row.label}
                                        </dt>
                                        <dd className="min-w-0 text-right text-sm font-medium tabular-nums text-foreground">
                                            {row.value}
                                        </dd>
                                    </div>
                                ))}
                            </dl>
                        </div>
                    </div>
                </div>
            </main>
            <LandingFooter />
        </div>
    );
}
