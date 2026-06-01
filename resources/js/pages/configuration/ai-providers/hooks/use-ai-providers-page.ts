import { setLayoutProps } from '@inertiajs/react';
import { useLayoutEffect, useMemo } from 'react';
import { dashboard } from '@/routes';
import { edit as aiProvidersEdit } from '@/routes/configuration/ai-providers';

export function useAiProvidersPage() {
    const breadcrumbs = useMemo(
        () => [
            { title: 'Panel', href: dashboard() },
            { title: 'Proveedores de IA', href: aiProvidersEdit() },
        ],
        [],
    );

    useLayoutEffect(() => {
        setLayoutProps({ breadcrumbs });
    }, [breadcrumbs]);
}
