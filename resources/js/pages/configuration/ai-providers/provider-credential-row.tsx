import { DateDisplay } from '@/components/custom/date-display';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { AiProviderDefinition } from '@/pages/configuration/ai-providers/types';

type ProviderCredentialRowProps = {
    provider: AiProviderDefinition;
    canUpdate: boolean;
    onModify: (provider: AiProviderDefinition) => void;
};

export function ProviderCredentialRow({
    provider,
    canUpdate,
    onModify,
}: ProviderCredentialRowProps) {
    const hasPanelMetadata =
        provider.keyConfigured &&
        provider.keyLastChars !== null &&
        provider.keyUpdatedAt !== null;

    return (
        <div className="flex flex-col gap-3 border-b border-border pb-4 last:border-b-0 last:pb-0 lg:flex-row lg:items-center lg:gap-4">
            <p className="w-full shrink-0 text-sm font-medium lg:w-36">
                {provider.label}
            </p>

            <div className="flex min-w-0 flex-1 flex-col gap-1 sm:flex-row sm:items-center sm:gap-3">
                {hasPanelMetadata ? (
                    <p className="text-muted-foreground text-sm">
                        <span className="font-mono">{provider.keyLastChars}</span>
                        <span className="mx-2 text-border">·</span>
                        <span>
                            Actualizado{' '}
                            <DateDisplay
                                value={provider.keyUpdatedAt}
                                mode="datetime"
                            />
                        </span>
                    </p>
                ) : provider.keyConfigured && provider.configuredViaEnvironment ? (
                    <p className="text-muted-foreground text-sm">
                        Configurado desde variables de entorno
                    </p>
                ) : (
                    <p className="text-muted-foreground text-sm">Sin credencial</p>
                )}

                {canUpdate ? (
                    <Button
                        type="button"
                        variant="link"
                        className="h-auto w-fit p-0"
                        onClick={() => onModify(provider)}
                    >
                        {provider.keyConfigured ? 'Modificar' : 'Agregar credencial'}
                    </Button>
                ) : null}
            </div>

            <Badge
                variant={provider.keyConfigured ? 'default' : 'secondary'}
                className="w-fit shrink-0"
            >
                {provider.keyConfigured ? 'Configurado' : 'Sin credenciales'}
            </Badge>
        </div>
    );
}
