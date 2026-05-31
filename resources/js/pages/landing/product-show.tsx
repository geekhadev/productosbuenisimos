import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { ChatbotWidget } from '@/components/chatbot/chatbot-widget';
import { ProductImageGallery } from '@/components/custom/product-image-gallery';
import { Button } from '@/components/ui/button';
import { formatLandingDimension } from '@/pages/landing/format-dimension';
import { formatLandingProductPrice } from '@/pages/landing/format-price';
import { LandingFooter } from '@/pages/landing/landing-footer';
import { LandingHeader } from '@/pages/landing/landing-header';
import type { PublicLandingProductDetail } from '@/pages/landing/types';
import { home } from '@/routes';

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
                        <ProductImageGallery
                            className="mx-auto w-full max-w-xl lg:max-w-none"
                            images={product.images}
                            productName={product.name}
                            video={product.video}
                        />
                        <div className="min-w-0">
                            <h1 className="mt-2 text-3xl font-semibold tracking-tight text-foreground sm:text-4xl">
                                {product.name}
                            </h1>
                            <p className="mt-6 text-2xl font-bold tracking-tight text-primary sm:text-3xl lg:text-4xl">
                                {priceLabel}
                            </p>
                            {product.description ? (
                                <p className="mt-4 text-base text-muted-foreground sm:text-lg">
                                    {product.description}
                                </p>
                            ) : null}
                            <dl className="mt-6 rounded-lg border border-border/60 bg-card/40 px-3 py-0.5 w-42">
                                {rows.map((row) => (
                                    <div
                                        key={row.label}
                                        className="flex min-h-8 items-baseline gap-3 border-b border-border/50 justify-between py-1.5 last:border-b-0"
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
            <ChatbotWidget
                product={{
                    id: product.id,
                    name: product.name,
                    code: product.code,
                    sku: product.sku,
                    price: priceToNumber(product.price),
                }}
            />
        </div>
    );
}
