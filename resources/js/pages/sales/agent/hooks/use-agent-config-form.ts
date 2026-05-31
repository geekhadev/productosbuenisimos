import { setLayoutProps, useForm } from '@inertiajs/react';
import { useCallback, useLayoutEffect, useMemo } from 'react';
import type {
    AgentConfigEditPageProps,
    AgentConfigFormData,
} from '@/pages/sales/agent/types';
import { dashboard } from '@/routes';
import { edit as agentEdit, update as agentUpdate } from '@/routes/sales/agent';

function defaultModelForProvider(
    providerModels: AgentConfigEditPageProps['providerModels'],
    provider: string,
): string {
    return providerModels[provider]?.[0]?.value ?? '';
}

function buildFormDefaults(props: AgentConfigEditPageProps): AgentConfigFormData {
    return {
        enabled_tools: props.enabledTools ?? [],
        provider: props.provider,
        model: props.model || defaultModelForProvider(props.providerModels, props.provider),
        prompt: props.prompt ?? '',
        use_default_prompt: !props.usesCustomPrompt,
    };
}

export function useAgentConfigForm(props: AgentConfigEditPageProps) {
    const formDefaults = useMemo(() => buildFormDefaults(props), [props]);
    const form = useForm<AgentConfigFormData>(formDefaults);
    const { put } = form;

    const breadcrumbs = useMemo(
        () => [
            { title: 'Panel', href: dashboard() },
            { title: 'Agente de Ventas', href: agentEdit() },
        ],
        [],
    );

    useLayoutEffect(() => {
        setLayoutProps({ breadcrumbs });
    }, [breadcrumbs]);

    const enabledTools = useMemo(
        () => form.data.enabled_tools ?? [],
        [form.data.enabled_tools],
    );
    const enabledToolsCount = enabledTools.length;

    const isToolEnabled = useCallback(
        (slug: string) => enabledTools.includes(slug),
        [enabledTools],
    );

    const toggleTool = useCallback(
        (slug: string, enabled: boolean) => {
            if (!props.can.update) {
                return;
            }

            const current = form.data.enabled_tools ?? [];

            if (enabled) {
                if (current.includes(slug)) {
                    return;
                }

                form.setData('enabled_tools', [...current, slug]);

                return;
            }

            if (current.length <= 1) {
                return;
            }

            form.setData(
                'enabled_tools',
                current.filter((item) => item !== slug),
            );
        },
        [form, props.can.update],
    );

    const onPromptChange = useCallback(
        (value: string) => {
            form.setData((data) => ({
                ...data,
                prompt: value,
                use_default_prompt: false,
            }));
        },
        [form],
    );

    const onProviderChange = useCallback(
        (provider: string) => {
            form.setData((data) => ({
                ...data,
                provider,
                model: defaultModelForProvider(props.providerModels, provider),
            }));
        },
        [form, props.providerModels],
    );

    const submit = useCallback(
        (e: React.FormEvent) => {
            e.preventDefault();

            if (!props.can.update) {
                return;
            }

            put(agentUpdate.url(), { preserveScroll: true });
        },
        [props.can.update, put],
    );

    return {
        form,
        submit,
        enabledToolsCount,
        isToolEnabled,
        toggleTool,
        onPromptChange,
        onProviderChange,
    };
}
