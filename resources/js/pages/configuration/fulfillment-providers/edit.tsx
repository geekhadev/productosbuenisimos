import { Head } from '@inertiajs/react';
import { FulfillmentProvidersSidebar } from '@/pages/configuration/fulfillment-providers/fulfillment-providers-sidebar';
import { useFulfillmentProvidersPage } from '@/pages/configuration/fulfillment-providers/hooks/use-fulfillment-providers-page';
import { useProviderCredentialModal } from '@/pages/configuration/fulfillment-providers/hooks/use-provider-credential-modal';
import { ProviderCredentialModal } from '@/pages/configuration/fulfillment-providers/provider-credential-modal';
import { ProviderCredentialRow } from '@/pages/configuration/fulfillment-providers/provider-credential-row';
import type { FulfillmentProvidersEditPageProps } from '@/pages/configuration/fulfillment-providers/types';

function FulfillmentProvidersEdit(props: FulfillmentProvidersEditPageProps) {
    useFulfillmentProvidersPage();
    const credentialModal = useProviderCredentialModal(props.can.update);
    const canUpdate = props.can.update;

    return (
        <>
            <Head title="Proveedores de fulfillment" />

            <div className="flex min-w-0 flex-1 flex-row items-start gap-12 p-4 max-w-[1400px]">
                <FulfillmentProvidersSidebar />

                <section className="min-w-0 flex-1 space-y-4">
                    {props.providers.map((provider) => (
                        <ProviderCredentialRow
                            key={provider.slug}
                            provider={provider}
                            canUpdate={canUpdate}
                            onModify={credentialModal.openForProvider}
                        />
                    ))}
                </section>
            </div>

            <ProviderCredentialModal modal={credentialModal} />
        </>
    );
}

export default FulfillmentProvidersEdit;
