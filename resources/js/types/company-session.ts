/** Empresa activa persistida en sesión (backend `company_selected`). */
export type CompanySelectedSession = {
    id: string;
    document_type: string | null;
    document_number: string | null;
    name: string;
    alias: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
};

export type SelectableCompanyOption = {
    id: string;
    name: string;
    alias: string | null;
    document_type: string | null;
    document_number: string | null;
};
