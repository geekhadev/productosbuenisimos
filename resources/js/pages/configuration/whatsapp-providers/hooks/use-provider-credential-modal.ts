import { useForm } from '@inertiajs/react';
import { useCallback, useState } from 'react';
import type {
    ProviderCredentialFormData,
    WhatsappProviderDefinition,
} from '@/pages/configuration/whatsapp-providers/types';
import { update as updateProviderCredential } from '@/routes/configuration/whatsapp-providers/credential';

function buildInitialFormData(
    provider: WhatsappProviderDefinition,
): ProviderCredentialFormData {
    return Object.fromEntries(
        provider.fields.map((field) => [
            field.key,
            field.type === 'text' ? (field.value ?? '') : '',
        ]),
    );
}

export function useProviderCredentialModal(canUpdate: boolean) {
    const [open, setOpen] = useState(false);
    const [provider, setProvider] =
        useState<WhatsappProviderDefinition | null>(null);

    const form = useForm<ProviderCredentialFormData>({});

    const openForProvider = useCallback(
        (next: WhatsappProviderDefinition) => {
            if (!canUpdate) {
                return;
            }

            setProvider(next);
            form.setData(buildInitialFormData(next));
            form.clearErrors();
            setOpen(true);
        },
        [canUpdate, form],
    );

    const handleOpenChange = useCallback(
        (nextOpen: boolean) => {
            setOpen(nextOpen);

            if (!nextOpen) {
                setProvider(null);
                form.reset();
                form.clearErrors();
            }
        },
        [form],
    );

    const submit = useCallback(
        (e: React.FormEvent) => {
            e.preventDefault();

            if (!canUpdate || provider === null) {
                return;
            }

            form.put(updateProviderCredential.url(provider.slug), {
                preserveScroll: true,
                onSuccess: () => {
                    handleOpenChange(false);
                },
            });
        },
        [canUpdate, form, handleOpenChange, provider],
    );

    return {
        open,
        provider,
        form,
        openForProvider,
        handleOpenChange,
        submit,
    };
}
