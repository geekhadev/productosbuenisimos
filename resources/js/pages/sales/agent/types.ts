export type AgentToolDefinition = {
    slug: string;
    label: string;
    description: string;
};

export type AgentProviderOption = {
    value: string;
    label: string;
};

export type AgentConfigFormData = {
    enabled_tools: string[];
    provider: string;
    model: string;
    prompt: string;
    use_default_prompt: boolean;
};

export type AgentConfigEditPageProps = {
    tools: AgentToolDefinition[];
    enabledTools: string[];
    provider: string;
    model: string;
    providers: AgentProviderOption[];
    prompt: string;
    usesCustomPrompt: boolean;
    can: {
        update: boolean;
    };
};
