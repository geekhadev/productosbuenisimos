import { GoogleIntegrationSubtab } from '@/pages/configuration/companies/tabs/integrations/google-integration-subtab';
import { PlaceholderTabPanel } from '@/pages/configuration/companies/tabs/placeholder-tab-panel';

type IntegrationsTabPanelProps = {
    companyId: string | null;
};

export function IntegrationsTabPanel({ companyId }: IntegrationsTabPanelProps) {
    if (companyId === null) {
        return (
            <PlaceholderTabPanel message="Guarda la empresa primero para configurar integraciones." />
        );
    }

    return (
        <div className="w-full space-y-6">
            <div>
                <h2 className="text-lg font-semibold tracking-tight">
                    Integraciones
                </h2>
                <p className="text-muted-foreground text-sm">
                    Conecta servicios externos asociados a esta empresa.
                </p>
            </div>

            <GoogleIntegrationSubtab />
        </div>
    );
}
