import { usePage } from '@inertiajs/react';

export function LandingFooter() {
    const { name } = usePage().props;
    const year = new Date().getFullYear();

    return (
        <footer className="border-t border-border/60 bg-background px-4 py-10 sm:px-6 lg:px-8">
            <div className="mx-auto flex max-w-6xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p className="text-sm text-muted-foreground">
                    © {year} {name}. Todos los derechos reservados.
                </p>
            </div>
        </footer>
    );
}
