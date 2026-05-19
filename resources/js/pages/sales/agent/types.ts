export type AgentToolDefinition = {
    slug: string;
    label: string;
    description: string;
};

export type AgentConfigFormData = {
    enabled_tools: string[];
    prompt: string;
    use_default_prompt: boolean;
};

export type AgentConfigEditPageProps = {
    tools: AgentToolDefinition[];
    enabledTools: string[];
    prompt: string;
    usesCustomPrompt: boolean;
    can: {
        update: boolean;
    };
};
