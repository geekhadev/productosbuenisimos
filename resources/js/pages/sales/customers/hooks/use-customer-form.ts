import { setLayoutProps, useForm } from '@inertiajs/react';
import { useCallback, useEffect, useLayoutEffect, useMemo } from 'react';
import type {
    CustomerAddressLineFormData,
    CustomerFormData,
    CustomerFormRecord,
    CustomersFormPageProps,
} from '@/pages/sales/customers/types';
import { dashboard } from '@/routes';
import {
    create as customersCreate,
    edit,
    index as customersIndex,
    store,
    update,
} from '@/routes/sales/customers';

export function emptyAddressLine(): CustomerAddressLineFormData {
    return {
        id: null,
        country_name: '',
        state_name: '',
        address: '',
    };
}

function buildFormDefaults(customer: CustomerFormRecord | null): CustomerFormData {
    const addresses: CustomerAddressLineFormData[] =
        customer?.addresses != null && customer.addresses.length > 0
            ? customer.addresses.map((row) => ({
                  id: row.id,
                  country_name: row.country_name,
                  state_name: row.state_name,
                  address: row.address,
              }))
            : [];

    return {
        full_name: customer?.full_name ?? '',
        phone: customer?.phone ?? '',
        addresses,
    };
}

export function useCustomerForm({ customer }: CustomersFormPageProps) {
    const isEdit = customer != null;
    const customerId = customer?.id;
    const customerLabel = customer?.full_name;

    const formDefaults = useMemo(() => buildFormDefaults(customer), [customer]);

    const formDefaultsSyncKey = useMemo(
        () => JSON.stringify(formDefaults),
        [formDefaults],
    );

    const form = useForm<CustomerFormData>(formDefaults);
    const { setDefaults, reset, patch, post, setData } = form;

    useEffect(() => {
        const defaults = JSON.parse(formDefaultsSyncKey) as CustomerFormData;
        setDefaults(defaults);
        reset();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [formDefaultsSyncKey]);

    const breadcrumbs = useMemo(
        () => [
            { title: 'Panel', href: dashboard() },
            { title: 'Clientes', href: customersIndex() },
            {
                title: isEdit && customerLabel != null ? customerLabel : 'Nuevo',
                href:
                    isEdit && customerId != null
                        ? edit.url(customerId)
                        : customersCreate(),
            },
        ],
        [isEdit, customerId, customerLabel],
    );

    useLayoutEffect(() => {
        setLayoutProps({ breadcrumbs });
    }, [breadcrumbs]);

    const headTitle = isEdit ? 'Editar cliente' : 'Nuevo cliente';

    const addAddressLine = useCallback(() => {
        setData('addresses', [...form.data.addresses, emptyAddressLine()]);
    }, [form.data.addresses, setData]);

    const removeAddressLine = useCallback(
        (index: number) => {
            setData(
                'addresses',
                form.data.addresses.filter((_, i) => i !== index),
            );
        },
        [form.data.addresses, setData],
    );

    const updateAddressLine = useCallback(
        (index: number, patchRow: Partial<CustomerAddressLineFormData>) => {
            const next = form.data.addresses.map((row, i) =>
                i === index ? { ...row, ...patchRow } : row,
            );
            setData('addresses', next);
        },
        [form.data.addresses, setData],
    );

    const submit = useCallback(
        (e: React.FormEvent) => {
            e.preventDefault();

            if (isEdit && customerId != null) {
                patch(update.url(customerId), { preserveScroll: true });

                return;
            }

            post(store.url(), { preserveScroll: true });
        },
        [isEdit, patch, post, customerId],
    );

    return {
        form,
        submit,
        headTitle,
        isEdit,
        addAddressLine,
        removeAddressLine,
        updateAddressLine,
    };
}
