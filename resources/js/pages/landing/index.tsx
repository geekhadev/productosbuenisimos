import { Head } from '@inertiajs/react';
import { LandingCta } from '@/pages/landing/landing-cta';
import { LandingFeaturedProducts } from '@/pages/landing/landing-featured-products';
import { LandingFooter } from '@/pages/landing/landing-footer';
import { LandingHeader } from '@/pages/landing/landing-header';
import { LandingHero } from '@/pages/landing/landing-hero';

export default function LandingIndex({
    canRegister = true,
}: {
    canRegister?: boolean;
}) {
    return (
        <div id="top" className="min-h-svh bg-background text-foreground">
            <Head title="Productos Buenísimos — Tienda" />
            <LandingHeader canRegister={canRegister} />
            <main>
                <LandingHero canRegister={canRegister} />
                <LandingFeaturedProducts />
                <LandingCta canRegister={canRegister} />
            </main>
            <LandingFooter />
        </div>
    );
}
