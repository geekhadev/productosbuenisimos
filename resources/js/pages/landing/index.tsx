import { Head } from '@inertiajs/react';
import { LandingCta } from '@/pages/landing/landing-cta';
import { LandingFeaturedProducts } from '@/pages/landing/landing-featured-products';
import { LandingFooter } from '@/pages/landing/landing-footer';
import { LandingHeader } from '@/pages/landing/landing-header';
import { LandingHero } from '@/pages/landing/landing-hero';
import type { LandingProduct } from '@/pages/landing/types';

type LandingPageProps = {
    canRegister?: boolean;
    heroProduct: LandingProduct | null;
    showcaseProducts: LandingProduct[];
};

export default function LandingIndex({
    canRegister = true,
    heroProduct,
    showcaseProducts,
}: LandingPageProps) {
    return (
        <div id="top" className="min-h-svh bg-background text-foreground">
            <Head title="Productos Buenísimos — Tienda" />
            <LandingHeader canRegister={canRegister} />
            <main>
                <LandingHero heroProduct={heroProduct} />
                <LandingFeaturedProducts showcaseProducts={showcaseProducts} />
                <LandingCta canRegister={canRegister} />
            </main>
            <LandingFooter />
        </div>
    );
}
