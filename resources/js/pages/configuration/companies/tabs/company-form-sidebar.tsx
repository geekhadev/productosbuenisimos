import {
    Building2,
    ChevronLeft,
    Plug2,
    ReceiptText,
    Trash2,
} from 'lucide-react';
import { FormActionButton } from '@/components/custom/form-action-button';
import { FormLinkButton } from '@/components/custom/form-link-button';
import { cn } from '@/lib/utils';
import { COMPANY_FORM_TABS } from '@/pages/configuration/companies/tabs/tab-config';
import type { CompanyFormTabId } from '@/pages/configuration/companies/types';
import { index as companiesIndex } from '@/routes/configuration/companies';

function companyFormTabIcon(tabId: CompanyFormTabId) {
    const className = 'size-4 shrink-0';

    switch (tabId) {
        case 'general':
            return <Building2 aria-hidden className={className} />;
        case 'integraciones':
            return <Plug2 aria-hidden className={className} />;
        case 'facturacion':
            return <ReceiptText aria-hidden className={className} />;
        case 'eliminar':
            return <Trash2 aria-hidden className={className} />;
    }
}

type CompanyFormSidebarProps = {
    activeTab: CompanyFormTabId;
    onTabChange: (tab: CompanyFormTabId) => void;
    isEdit: boolean;
};

export function CompanyFormSidebar({
    activeTab,
    onTabChange,
    isEdit,
}: CompanyFormSidebarProps) {
    return (
        <div className="flex max-w-xs flex-col gap-4">
            <h1 className="text-2xl font-semibold tracking-tight">Empresas</h1>
            <p className="text-muted-foreground text-sm">
                Agrega las diferentes empresas que gestiona.
            </p>
            <p className="text-muted-foreground text-sm">
                Ten en cuenta que cada empresa debe ser un RUT diferente y las
                configuraciones e información de cada empresa se gestionan por
                separado.
            </p>
            {COMPANY_FORM_TABS.filter((t) => isEdit || !t.editOnly).map((t) => {
                const isSecondaryTab = t.id !== 'general';
                const isTabLocked = !isEdit && isSecondaryTab;
                const isDeleteTab = t.id === 'eliminar';
                const isActive = activeTab === t.id;

                const variant = isDeleteTab
                    ? isActive
                        ? 'destructive'
                        : 'outline'
                    : isActive
                      ? 'default'
                      : 'outline';

                return (
                    <FormActionButton
                        key={t.id}
                        type="button"
                        size="lg"
                        variant={variant}
                        icon={companyFormTabIcon(t.id)}
                        label={t.label}
                        containerClassName="w-full"
                        disabled={isTabLocked}
                        title={
                            isTabLocked
                                ? 'Guarda la empresa para acceder a esta pestaña.'
                                : undefined
                        }
                        buttonClassName={cn(
                            'w-full justify-start text-left',
                            isTabLocked && 'cursor-not-allowed opacity-50',
                            !isDeleteTab &&
                                !isTabLocked &&
                                !isActive &&
                                'text-muted-foreground',
                            isDeleteTab &&
                                !isActive &&
                                'border-destructive/40 text-destructive hover:bg-destructive/10 hover:text-destructive',
                        )}
                        onClick={() => onTabChange(t.id)}
                    />
                );
            })}
            <FormLinkButton
                href={companiesIndex.url()}
                buttonVariant="ghost"
                icon={<ChevronLeft />}
                label="Volver a la lista de empresas"
                containerClassName="w-full"
            />
        </div>
    );
}
