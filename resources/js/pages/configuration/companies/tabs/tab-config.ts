import type { CompanyFormTabId } from '@/pages/configuration/companies/types';

export const DOCUMENT_OPTIONS = [
    { id: 'RUT', label: 'RUT' },
    { id: 'Pasaporte', label: 'Pasaporte' },
] as const;

export type CompanyFormTabConfig = {
    id: CompanyFormTabId;
    label: string;
    /** Solo en modo edición (empresa ya guardada). */
    editOnly?: boolean;
};

export const COMPANY_FORM_TABS: ReadonlyArray<CompanyFormTabConfig> = [
    { id: 'general', label: 'Información general' },
    { id: 'integraciones', label: 'Integraciones' },
    { id: 'facturacion', label: 'Facturación' },
    { id: 'eliminar', label: 'Eliminar empresa', editOnly: true },
];
