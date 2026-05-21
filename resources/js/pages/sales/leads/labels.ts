export const LEAD_SOURCE_LABELS: Record<string, string> = {
    web: 'Web',
    whatsapp: 'WhatsApp',
    facebook: 'Facebook',
    instagram: 'Instagram',
    manual: 'Manual',
};

export const LEAD_STATUS_LABELS: Record<string, string> = {
    nuevo: 'Nuevo',
    contactado: 'Contactado',
    convertido: 'Convertido',
    inactivo: 'Inactivo',
};

export const LEAD_SOURCE_OPTIONS = [
    { id: '', label: 'Todas' },
    { id: 'web', label: LEAD_SOURCE_LABELS.web },
    { id: 'whatsapp', label: LEAD_SOURCE_LABELS.whatsapp },
    { id: 'facebook', label: LEAD_SOURCE_LABELS.facebook },
    { id: 'instagram', label: LEAD_SOURCE_LABELS.instagram },
    { id: 'manual', label: LEAD_SOURCE_LABELS.manual },
] as const;

export const LEAD_STATUS_OPTIONS = [
    { id: '', label: 'Todos' },
    { id: 'nuevo', label: LEAD_STATUS_LABELS.nuevo },
    { id: 'contactado', label: LEAD_STATUS_LABELS.contactado },
    { id: 'convertido', label: LEAD_STATUS_LABELS.convertido },
    { id: 'inactivo', label: LEAD_STATUS_LABELS.inactivo },
] as const;

export const LEAD_SOURCE_FORM_OPTIONS = LEAD_SOURCE_OPTIONS.filter((o) => o.id !== '');

export const LEAD_STATUS_FORM_OPTIONS = LEAD_STATUS_OPTIONS.filter((o) => o.id !== '');
