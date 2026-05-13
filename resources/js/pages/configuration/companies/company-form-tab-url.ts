import type { CompanyFormTabId } from '@/pages/configuration/companies/types';

export const COMPANY_FORM_TAB_QUERY_KEY = 'tab' as const;

const COMPANY_FORM_TAB_IDS = [
    'general',
    'integraciones',
    'facturacion',
    'eliminar',
] as const satisfies readonly CompanyFormTabId[];

export function isCompanyFormTabId(value: string): value is CompanyFormTabId {
    return (COMPANY_FORM_TAB_IDS as readonly string[]).includes(value);
}

/**
 * Resuelve la pestaña activa desde la URL de la página Inertia (`page.url`, path + query).
 */
export function parseCompanyFormTabFromPageUrl(
    pageUrl: string,
    isEdit: boolean,
): CompanyFormTabId {
    const raw = new URL(pageUrl, 'http://localhost').searchParams.get(
        COMPANY_FORM_TAB_QUERY_KEY,
    );

    if (!raw) {
        return 'general';
    }

    if (!isCompanyFormTabId(raw)) {
        return 'general';
    }

    if (!isEdit && raw !== 'general') {
        return 'general';
    }

    return raw;
}
