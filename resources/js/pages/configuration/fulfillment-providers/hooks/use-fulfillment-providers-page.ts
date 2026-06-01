import { setLayoutProps } from '@inertiajs/react';
import { useLayoutEffect, useMemo } from 'react';
import { dashboard } from '@/routes';
import { edit as fulfillmentProvidersEdit } from '@/routes/configuration/fulfillment-providers';

export function useFulfillmentProvidersPage() {
    const breadcrumbs = useMemo(
        () => [
            { title: 'Panel', href: dashboard() },
            {
                title: 'Proveedores de fulfillment',
                href: fulfillmentProvidersEdit(),
            },
        ],
        [],
    );

    useLayoutEffect(() => {
        setLayoutProps({ breadcrumbs });
    }, [breadcrumbs]);
}
