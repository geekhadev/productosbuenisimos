import { Head } from '@inertiajs/react';
import { useProviderCredentialModal } from '@/pages/configuration/whatsapp-providers/hooks/use-provider-credential-modal';
import { useWhatsappProvidersPage } from '@/pages/configuration/whatsapp-providers/hooks/use-whatsapp-providers-page';
import { ProviderCredentialModal } from '@/pages/configuration/whatsapp-providers/provider-credential-modal';
import { ProviderCredentialRow } from '@/pages/configuration/whatsapp-providers/provider-credential-row';
import type { WhatsappProvidersEditPageProps } from '@/pages/configuration/whatsapp-providers/types';
import { WhatsappProvidersSidebar } from '@/pages/configuration/whatsapp-providers/whatsapp-providers-sidebar';

function WhatsappProvidersEdit(props: WhatsappProvidersEditPageProps) {
    useWhatsappProvidersPage();
    const credentialModal = useProviderCredentialModal(props.can.update);
    const canUpdate = props.can.update;

    return (
        <>
            <Head title="Proveedores de WhatsApp" />

            <div className="flex min-w-0 flex-1 flex-row items-start gap-12 p-4 max-w-[1400px]">
                <WhatsappProvidersSidebar />

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

export default WhatsappProvidersEdit;
