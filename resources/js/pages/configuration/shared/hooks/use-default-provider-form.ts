import { useForm } from '@inertiajs/react';
import { useCallback, useEffect } from 'react';

type UseDefaultProviderFormOptions = {
    canUpdate: boolean;
    defaultProvider: string | null;
    updateUrl: string;
};

export function useDefaultProviderForm({
    canUpdate,
    defaultProvider,
    updateUrl,
}: UseDefaultProviderFormOptions) {
    const form = useForm({
        provider: defaultProvider ?? '',
    });

    useEffect(() => {
        form.setData('provider', defaultProvider ?? '');
    }, [defaultProvider, form]);

    const submitProvider = useCallback(
        (provider: string) => {
            if (!canUpdate || provider === '' || provider === defaultProvider) {
                return;
            }

            form.setData('provider', provider);
            form.put(updateUrl, {
                preserveScroll: true,
            });
        },
        [canUpdate, defaultProvider, form, updateUrl],
    );

    return {
        form,
        submitProvider,
    };
}
