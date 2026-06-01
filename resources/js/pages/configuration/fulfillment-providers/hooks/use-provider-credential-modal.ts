import { useForm } from '@inertiajs/react';
import { useCallback, useState } from 'react';
import type {
    FulfillmentProviderDefinition,
    ProviderCredentialFormData,
} from '@/pages/configuration/fulfillment-providers/types';
import { update as updateProviderCredential } from '@/routes/configuration/fulfillment-providers/credential';

export function useProviderCredentialModal(canUpdate: boolean) {
    const [open, setOpen] = useState(false);
    const [provider, setProvider] =
        useState<FulfillmentProviderDefinition | null>(null);

    const form = useForm<ProviderCredentialFormData>({
        api_url: '',
        user: '',
        pass: '',
    });

    const openForProvider = useCallback(
        (next: FulfillmentProviderDefinition) => {
            if (!canUpdate) {
                return;
            }

            setProvider(next);
            form.setData({
                api_url: next.apiUrl ?? '',
                user: next.user ?? '',
                pass: '',
            });
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
