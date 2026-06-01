import { Head } from '@inertiajs/react';
import { AiProvidersSidebar } from '@/pages/configuration/ai-providers/ai-providers-sidebar';
import { useAiProvidersPage } from '@/pages/configuration/ai-providers/hooks/use-ai-providers-page';
import { useProviderCredentialModal } from '@/pages/configuration/ai-providers/hooks/use-provider-credential-modal';
import { ProviderCredentialModal } from '@/pages/configuration/ai-providers/provider-credential-modal';
import { ProviderCredentialRow } from '@/pages/configuration/ai-providers/provider-credential-row';
import type { AiProvidersEditPageProps } from '@/pages/configuration/ai-providers/types';
import {
    DefaultProviderSectionHeader,
    isDefaultProviderSelectable,
} from '@/pages/configuration/shared/default-provider-section-header';
import { useDefaultProviderForm } from '@/pages/configuration/shared/hooks/use-default-provider-form';
import { update as updateDefaultProvider } from '@/routes/configuration/ai-providers/default-provider';

function AiProvidersEdit(props: AiProvidersEditPageProps) {
    useAiProvidersPage();
    const credentialModal = useProviderCredentialModal(props.can.update);
    const canUpdate = props.can.update;
    const defaultProviderForm = useDefaultProviderForm({
        canUpdate,
        defaultProvider: props.defaultProvider,
        updateUrl: updateDefaultProvider.url(),
    });

    return (
        <>
            <Head title="Proveedores de IA" />

            <div className="flex min-w-0 flex-1 flex-row items-start gap-12 p-4 max-w-[1400px]">
                <AiProvidersSidebar />

                <section className="min-w-0 flex-1 space-y-4">
                    <DefaultProviderSectionHeader
                        label="Proveedor a usar"
                        emptyMessage="Agrega al menos una credencial para elegir el proveedor predeterminado."
                        error={defaultProviderForm.form.errors.provider}
                        hasSelectableProviders={
                            props.defaultProviderOptions.length > 0
                        }
                    />

                    {props.providers.map((provider) => (
                        <ProviderCredentialRow
                            key={provider.slug}
                            provider={provider}
                            canUpdate={canUpdate}
                            onModify={credentialModal.openForProvider}
                            defaultProvider={
                                defaultProviderForm.form.data.provider ||
                                props.defaultProvider
                            }
                            isDefaultSelectable={isDefaultProviderSelectable(
                                provider.slug,
                                props.defaultProviderOptions,
                            )}
                            isDefaultSelectionDisabled={
                                !canUpdate ||
                                defaultProviderForm.form.processing
                            }
                            onSelectDefault={
                                defaultProviderForm.submitProvider
                            }
                        />
                    ))}
                </section>
            </div>

            <ProviderCredentialModal modal={credentialModal} />
        </>
    );
}

export default AiProvidersEdit;
