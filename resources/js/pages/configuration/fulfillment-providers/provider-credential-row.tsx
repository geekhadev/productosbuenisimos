import { DateDisplay } from '@/components/custom/date-display';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { FulfillmentProviderDefinition } from '@/pages/configuration/fulfillment-providers/types';

type ProviderCredentialRowProps = {
    provider: FulfillmentProviderDefinition;
    canUpdate: boolean;
    onModify: (provider: FulfillmentProviderDefinition) => void;
};

export function ProviderCredentialRow({
    provider,
    canUpdate,
    onModify,
}: ProviderCredentialRowProps) {
    const hasPanelMetadata =
        provider.credentialsConfigured &&
        provider.apiUrl !== null &&
        provider.user !== null &&
        provider.passLastChars !== null &&
        provider.credentialsUpdatedAt !== null;

    return (
        <div className="flex flex-col gap-2 border-b border-border pb-4 last:border-b-0 last:pb-0">
            <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                <p className="text-sm font-medium">{provider.label}</p>

                <div className="flex flex-wrap items-center gap-3">
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
            </div>

            {hasPanelMetadata ? (
                <div className="text-muted-foreground space-y-1 text-sm">
                    <p className="break-all font-mono">{provider.apiUrl}</p>
                    <p>
                        <span>Usuario: </span>
                        <span className="font-mono">{provider.user}</span>
                        <span className="mx-2 text-border">·</span>
                        <span>Contraseña: </span>
                        <span className="font-mono">{provider.passLastChars}</span>
                        <span className="mx-2 text-border">·</span>
                        <span>
                            Actualizado{' '}
                            <DateDisplay
                                value={provider.credentialsUpdatedAt}
                                mode="datetime"
                            />
                        </span>
                    </p>
                </div>
            ) : (
                <p className="text-muted-foreground text-sm">Sin credencial</p>
            )}
        </div>
    );
}
