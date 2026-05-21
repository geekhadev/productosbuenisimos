import { setLayoutProps, useForm } from '@inertiajs/react';
import { useCallback, useEffect, useLayoutEffect, useMemo } from 'react';
import type {
    LeadFormData,
    LeadFormRecord,
    LeadsFormPageProps,
} from '@/pages/sales/leads/types';
import { dashboard } from '@/routes';
import {
    create as leadsCreate,
    edit,
    index as leadsIndex,
    store,
    update,
} from '@/routes/sales/leads';

function buildFormDefaults(lead: LeadFormRecord | null): LeadFormData {
    return {
        phone: lead?.phone ?? '',
        source: lead?.source ?? 'manual',
        status: lead?.status ?? 'nuevo',
        customer_id: lead?.customer_id ?? '',
    };
}

export function useLeadForm({ lead, customers }: LeadsFormPageProps) {
    const isEdit = lead != null;
    const leadId = lead?.id;

    const formDefaults = useMemo(() => buildFormDefaults(lead), [lead]);

    const formDefaultsSyncKey = useMemo(
        () => JSON.stringify(formDefaults),
        [formDefaults],
    );

    const form = useForm<LeadFormData>(formDefaults);
    const { setDefaults, reset, patch, post, setData } = form;

    useEffect(() => {
        const defaults = JSON.parse(formDefaultsSyncKey) as LeadFormData;
        setDefaults(defaults);
        reset();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [formDefaultsSyncKey]);

    const customerOptions = useMemo(
        () => [
            { id: '', label: 'Sin cliente vinculado' },
            ...customers.map((c) => ({ id: c.id, label: c.label })),
        ],
        [customers],
    );

    const breadcrumbs = useMemo(
        () => [
            { title: 'Panel', href: dashboard() },
            { title: 'Leads', href: leadsIndex() },
            {
                title: isEdit ? lead?.phone ?? 'Editar' : 'Nuevo',
                href:
                    isEdit && leadId != null ? edit.url(leadId) : leadsCreate(),
            },
        ],
        [isEdit, leadId, lead?.phone],
    );

    useLayoutEffect(() => {
        setLayoutProps({ breadcrumbs });
    }, [breadcrumbs]);

    const headTitle = isEdit ? 'Editar lead' : 'Nuevo lead';

    const submit = useCallback(
        (e: React.FormEvent) => {
            e.preventDefault();

            if (isEdit && leadId != null) {
                patch(update.url(leadId), { preserveScroll: true });

                return;
            }

            post(store.url(), { preserveScroll: true });
        },
        [isEdit, patch, post, leadId],
    );

    return {
        form,
        submit,
        headTitle,
        isEdit,
        customerOptions,
        setData,
    };
}
