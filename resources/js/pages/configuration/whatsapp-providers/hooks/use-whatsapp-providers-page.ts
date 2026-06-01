import { setLayoutProps } from '@inertiajs/react';
import { useLayoutEffect, useMemo } from 'react';
import { dashboard } from '@/routes';
import { edit as whatsappProvidersEdit } from '@/routes/configuration/whatsapp-providers';

export function useWhatsappProvidersPage() {
    const breadcrumbs = useMemo(
        () => [
            { title: 'Panel', href: dashboard() },
            {
                title: 'Proveedores de WhatsApp',
                href: whatsappProvidersEdit(),
            },
        ],
        [],
    );

    useLayoutEffect(() => {
        setLayoutProps({ breadcrumbs });
    }, [breadcrumbs]);
}
