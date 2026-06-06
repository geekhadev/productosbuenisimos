import { DateDisplay } from '@/components/custom/date-display';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DefaultProviderRadio } from '@/pages/configuration/shared/default-provider-radio';
import type { WhatsappProviderDefinition } from '@/pages/configuration/whatsapp-providers/types';

type ProviderCredentialRowProps = {
    provider: WhatsappProviderDefinition;
    canUpdate: boolean;
    onModify: (provider: WhatsappProviderDefinition) => void;
    defaultProvider: string | null;
    isDefaultSelectable: boolean;
    isDefaultSelectionDisabled: boolean;
    onSelectDefault: (slug: string) => void;
};

export function ProviderCredentialRow({
    provider,
    canUpdate,
    onModify,
    defaultProvider,
    isDefaultSelectable,
    isDefaultSelectionDisabled,
    onSelectDefault,
}: ProviderCredentialRowProps) {
    const hasPanelMetadata =
        provider.credentialsConfigured &&
        provider.credentialsUpdatedAt !== null &&
        provider.fields.some(
            (field) =>
                (field.type === 'text' && field.value !== null) ||
                (field.type === 'secret' && field.hint !== null),
        );

    return (
        <div className="flex flex-col gap-3 border-b border-border pb-4 last:border-b-0 last:pb-0 lg:flex-row lg:items-center lg:gap-4">
            <div className="flex items-center gap-3 lg:w-44">
                <DefaultProviderRadio
                    slug={provider.slug}
                    label={provider.label}
                    name="default-whatsapp-provider"
                    checked={defaultProvider === provider.slug}
                    disabled={
                        !isDefaultSelectable || isDefaultSelectionDisabled
                    }
                    onSelect={onSelectDefault}
                />
                <p className="text-sm font-medium">{provider.label}</p>
            </div>

            <div className="flex min-w-0 flex-1 flex-col gap-1 sm:flex-row sm:items-center sm:gap-3">
                {hasPanelMetadata ? (
                    <div className="text-muted-foreground space-y-1 text-sm">
                        {provider.fields.map((field) => {
                            if (field.type === 'text' && field.value !== null) {
                                return (
                                    <p key={field.key} className="break-all">
                                        <span>{field.label}: </span>
                                        <span className="font-mono">
                                            {field.value}
                                        </span>
                                    </p>
                                );
                            }

                            if (field.type === 'secret' && field.hint !== null) {
                                return (
                                    <p key={field.key}>
                                        <span>{field.label}: </span>
                                        <span className="font-mono">
                                            {field.hint}
                                        </span>
                                    </p>
                                );
                            }

                            return null;
                        })}
                        <p>
                            Actualizado{' '}
                            <DateDisplay
                                value={provider.credentialsUpdatedAt}
                                mode="datetime"
                            />
                        </p>
                    </div>
                ) : (
                    <p className="text-muted-foreground text-sm">
                        Sin credencial
                    </p>
                )}

                {canUpdate ? (
                    <Button
                        type="button"
                        variant="link"
                        className="h-auto w-fit p-0"
                        onClick={() => onModify(provider)}
                    >
                        {provider.credentialsConfigured
                            ? 'Modificar'
                            : 'Agregar credencial'}
                    </Button>
                ) : null}
            </div>

            <Badge
                variant={
                    provider.credentialsConfigured ? 'default' : 'secondary'
                }
                className="w-fit shrink-0"
            >
                {provider.credentialsConfigured
                    ? 'Configurado'
                    : 'Sin credenciales'}
            </Badge>
        </div>
    );
}
