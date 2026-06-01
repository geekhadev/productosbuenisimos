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
        const provider = defaultProvider ?? '';

        if (form.data.provider !== provider) {
            form.setData('provider', provider);
        }
        // Sync only when the server default changes, not on every form re-render.
        // eslint-disable-next-line react-hooks/exhaustive-deps -- form is intentionally omitted
    }, [defaultProvider]);

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
