export type AiProviderDefinition = {
    slug: string;
    label: string;
    keyConfigured: boolean;
    keyUpdatedAt: string | null;
    keyLastChars: string | null;
    configuredViaEnvironment: boolean;
};

export type ProviderCredentialFormData = {
    key: string;
};

export type AiProvidersEditPageProps = {
    providers: AiProviderDefinition[];
    can: {
        update: boolean;
    };
};
