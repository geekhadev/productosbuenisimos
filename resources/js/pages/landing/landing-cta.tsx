import { Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import type { LandingSectionProps } from '@/pages/landing/types';
import { dashboard, login } from '@/routes';

export function LandingCta({ canRegister }: LandingSectionProps) {
    const { auth } = usePage().props;

    return (
        <section className="border-y border-border/60 bg-muted/30 px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
            <div className="mx-auto flex max-w-6xl flex-col items-start justify-between gap-8 rounded-2xl border border-border/80 bg-card p-8 shadow-sm sm:flex-row sm:items-center sm:p-10 lg:p-12">
                <div>
                    <h2 className="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl">
                        ¿Listo para llenar el carrito?
                    </h2>
                    <p className="mt-2 max-w-xl text-muted-foreground">
                        Crea tu cuenta para guardar tus favoritos, repetir pedidos y enterarte de
                        novedades. Pronto sumaremos más formas de pago y envío.
                    </p>
                </div>
                <div className="flex w-full shrink-0 flex-col gap-3 sm:w-auto sm:flex-row">
                    {auth.user ? (
                        <Button size="lg" className="w-full sm:w-auto" asChild>
                            <Link href={dashboard()}>Ir a mi cuenta</Link>
                        </Button>
                    ) : canRegister ? (
                        <Button size="lg" className="w-full sm:w-auto" asChild>
                            <Link href="/register">Registrarse</Link>
                        </Button>
                    ) : (
                        <Button size="lg" className="w-full sm:w-auto" asChild>
                            <Link href={login()}>Iniciar sesión</Link>
                        </Button>
                    )}
                    <Button size="lg" variant="outline" className="w-full sm:w-auto" asChild>
                        <a href="#destacados">Ver productos</a>
                    </Button>
                </div>
            </div>
        </section>
    );
}
