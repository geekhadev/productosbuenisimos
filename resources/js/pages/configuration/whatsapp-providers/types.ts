export type WhatsappProviderFieldDefinition = {
    key: string;
    label: string;
    type: 'text' | 'secret';
    placeholder: string | null;
    value: string | null;
    hint: string | null;
};

export type WhatsappProviderDefinition = {
    slug: string;
    label: string;
    credentialsConfigured: boolean;
    credentialsUpdatedAt: string | null;
    configuredViaEnvironment: boolean;
    fields: WhatsappProviderFieldDefinition[];
};

export type ProviderCredentialFormData = Record<string, string>;

export type WhatsappProvidersEditPageProps = {
    providers: WhatsappProviderDefinition[];
    can: {
        update: boolean;
    };
};
