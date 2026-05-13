import { LandingProductCard } from '@/pages/landing/landing-product-card';
import { landingShowcaseProducts } from '@/pages/landing/landing-showcase-data';

export function LandingFeaturedProducts() {
    return (
        <section
            id="destacados"
            className="scroll-mt-20 border-b border-border/60 bg-muted/20 px-4 py-16 sm:px-6 sm:py-20 lg:px-8"
        >
            <div className="mx-auto max-w-6xl">
                <h2 className="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl">
                    Productos destacados
                </h2>
                <p className="mt-3 max-w-2xl text-muted-foreground">
                    Una selección de lo más pedido: ingredientes honestos, buen precio y envíos
                    pensados para que disfrutes sin complicarte.
                </p>
                <ul className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
                    {landingShowcaseProducts.map((product) => (
                        <li key={product.id}>
                            <LandingProductCard product={product} variant="grid" />
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}
