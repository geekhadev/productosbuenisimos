export type AiProviderDefinition = {
    slug: string;
    label: string;
    keyConfigured: boolean;
    keyUpdatedAt: string | null;
    keyLastChars: string | null;
};

export type ProviderCredentialFormData = {
    key: string;
};

export type DefaultProviderOption = {
    value: string;
    label: string;
};

export type AiProvidersEditPageProps = {
    providers: AiProviderDefinition[];
    defaultProvider: string | null;
    defaultProviderOptions: DefaultProviderOption[];
    can: {
        update: boolean;
    };
};
