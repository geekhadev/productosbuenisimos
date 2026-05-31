import { Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { LandingSectionProps } from '@/pages/landing/types';
import { dashboard, login } from '@/routes';

export function LandingHeader({ canRegister }: LandingSectionProps) {
    const { auth } = usePage().props;

    return (
        <header
            className={cn(
                'sticky top-0 z-50 w-full border-b border-border/60 bg-background/80 backdrop-blur-md',
            )}
        >
            <div className="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <a
                    href="#top"
                    className="flex shrink-0 items-center gap-2 rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background"
                >
                    <img
                        src="/assets/logo.png"
                        alt="Productos Buenisimos"
                        width={180}
                        height={48}
                        className="h-8 w-auto object-contain"
                    />
                </a>
                <nav
                    className="hidden items-center gap-6 text-sm font-medium text-muted-foreground md:flex"
                    aria-label="Secciones"
                >
                    <a
                        href="#destacados"
                        className="rounded-sm transition-colors hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background"
                    >
                        Tienda
                    </a>
                </nav>
                <div className="flex items-center gap-2 sm:gap-3">
                    {auth.user ? (
                        <Button asChild size="sm">
                            <Link href={dashboard()}>Mi cuenta</Link>
                        </Button>
                    ) : (
                        <>
                            {canRegister ? (
                                <Button size="sm" asChild>
                                    <Link href="/register">Crear cuenta</Link>
                                </Button>
                            ) : (
                                <Button size="sm" asChild>
                                    <Link href={login()}>Acceder</Link>
                                </Button>
                            )}
                        </>
                    )}
                </div>
            </div>
        </header>
    );
}
