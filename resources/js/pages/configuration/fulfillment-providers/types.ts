export type FulfillmentProviderDefinition = {
    slug: string;
    label: string;
    credentialsConfigured: boolean;
    credentialsUpdatedAt: string | null;
    apiUrl: string | null;
    user: string | null;
    passLastChars: string | null;
    configuredViaEnvironment: boolean;
};

export type ProviderCredentialFormData = {
    api_url: string;
    user: string;
    pass: string;
};

export type FulfillmentProvidersEditPageProps = {
    providers: FulfillmentProviderDefinition[];
    can: {
        update: boolean;
    };
};
