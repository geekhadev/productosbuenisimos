import { router, setLayoutProps, useForm, usePage } from '@inertiajs/react';
import { useCallback, useLayoutEffect, useMemo } from 'react';
import {
    COMPANY_FORM_TAB_QUERY_KEY,
    isCompanyFormTabId,
    parseCompanyFormTabFromPageUrl,
} from '@/pages/configuration/companies/company-form-tab-url';
import type {
    CompaniesFormPageProps,
    CompanyFormData,
    CompanyFormRecord,
} from '@/pages/configuration/companies/types';
import { dashboard } from '@/routes';
import {
    edit,
    index as companiesIndex,
    store,
    update,
} from '@/routes/configuration/companies';

function buildFormDefaults(company: CompanyFormRecord | null): CompanyFormData {
    return {
        document_type: company?.document_type ?? 'RUT',
        document_number: company?.document_number ?? '',
        name: company?.name ?? '',
        alias: company?.alias ?? '',
        email: company?.email ?? '',
        phone: company?.phone ?? '',
        address: company?.address ?? '',
    };
}

export function useCompanyForm({ company }: CompaniesFormPageProps) {
    const isEdit = company != null;
    const page = usePage();

    const formDefaults = useMemo(() => buildFormDefaults(company), [company]);

    const form = useForm<CompanyFormData>(formDefaults);

    useLayoutEffect(() => {
        if (company === null) {
            form.transform((data) => data);

            return;
        }

        form.transform((data) => {
            const {
                document_type: _documentType,
                document_number: _documentNumber,
                ...rest
            } = data;

            return rest;
        });
    }, [company, form]);

    const tab = useMemo(
        () => parseCompanyFormTabFromPageUrl(page.url, isEdit),
        [isEdit, page.url],
    );

    useLayoutEffect(() => {
        if (typeof window === 'undefined') {
            return;
        }

        if (!isEdit) {
            const url = new URL(window.location.href);

            if (!url.searchParams.has(COMPANY_FORM_TAB_QUERY_KEY)) {
                return;
            }

            url.searchParams.delete(COMPANY_FORM_TAB_QUERY_KEY);
            window.history.replaceState(
                window.history.state,
                '',
                `${url.pathname}${url.search}${url.hash}`,
            );

            return;
        }

        if (company === null) {
            return;
        }

        const raw = new URL(page.url, 'http://localhost').searchParams.get(
            COMPANY_FORM_TAB_QUERY_KEY,
        );

        if (raw !== null && !isCompanyFormTabId(raw)) {
            router.get(
                edit.url({ company: company.id }),
                {},
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }
    }, [company, isEdit, page.url]);

    const setTab = useCallback(
        (next: CompanyFormTabId) => {
            if (!isEdit && next !== 'general') {
                return;
            }

            if (company === null) {
                return;
            }

            const current = parseCompanyFormTabFromPageUrl(page.url, isEdit);

            if (current === next) {
                return;
            }

            const targetUrl =
                next === 'general'
                    ? edit.url({ company: company.id })
                    : edit.url(
                          { company: company.id },
                          {
                              query: {
                                  [COMPANY_FORM_TAB_QUERY_KEY]: next,
                              },
                          },
                      );

            router.get(targetUrl, {}, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        },
        [company, isEdit, page.url],
    );

    const breadcrumbs = useMemo(
        () => [
            { title: 'Panel', href: dashboard() },
            { title: 'Empresas', href: companiesIndex() },
            {
                title: isEdit && company ? company.name : 'Nueva',
                href: companiesIndex(),
            },
        ],
        [isEdit, company],
    );

    useLayoutEffect(() => {
        setLayoutProps({ breadcrumbs });
    }, [breadcrumbs]);

    const headTitle = isEdit ? 'Editar empresa' : 'Nueva empresa';

    const submit = (e: React.FormEvent): void => {
        e.preventDefault();

        if (isEdit && company) {
            form.patch(update.url(company.id), { preserveScroll: true });

            return;
        }

        form.post(store.url(), { preserveScroll: true });
    };

    return {
        tab,
        setTab,
        form,
        submit,
        headTitle,
        isEdit,
    };
}
