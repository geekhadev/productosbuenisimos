import { Head, Link, usePage } from '@inertiajs/react';
import { CirclePlus, PencilIcon, TrashIcon } from 'lucide-react';
import { useMemo } from 'react';
import { FormLinkButton } from '@/components/custom/form-link-button';
import {
    pickTabledataListShellConfig,
    TabledataProvider,
} from '@/components/custom/tabledata';
import type { TabledataColumn } from '@/components/custom/tabledata';
import { Button } from '@/components/ui/button';
import { CONFIG_TABLEDATA } from '@/pages/sales/customers/config';
import type { CustomersIndexPageProps } from '@/pages/sales/customers/config';
import { useCustomersIndex } from '@/pages/sales/customers/hooks/use-index';
import type {
    CustomerListFilters,
    CustomerRow,
    CustomersIndexFiltersDraftFull,
} from '@/pages/sales/customers/types';
import { create as customersCreate, edit } from '@/routes/sales/customers';

function CustomersIndex() {
    const page = usePage();
    const indexProps = page.props as unknown as CustomersIndexPageProps;
    const { deleteRow } = useCustomersIndex();

    const tableStorageKey = useMemo(() => {
        const userId = page.props.auth?.user?.id ?? 'anon';
        const companyId = page.props.company_selected?.id ?? 'none';

        return `sales-customers-${userId}-${companyId}`;
    }, [page.props.auth?.user?.id, page.props.company_selected?.id]);

    const columns = useMemo<TabledataColumn<CustomerRow>[]>(
        () => [
            {
                key: 'full_name',
                label: 'Nombre',
                sortable: true,
                hideable: false,
            },
            {
                key: 'phone',
                label: 'Teléfono',
                sortable: true,
                headerClassName: 'min-w-[120px]',
            },
            {
                key: 'actions',
                label: 'Acciones',
                hideable: false,
                headerClassName: 'w-0 text-right',
                render: (row) => (
                    <div className="flex justify-end gap-1">
                        {row.can.update ? (
                            <Button variant="outline" size="icon" type="button" asChild>
                                <Link
                                    href={edit.url(row.id)}
                                    aria-label={`Editar ${row.full_name}`}
                                >
                                    <PencilIcon className="size-3" />
                                </Link>
                            </Button>
                        ) : null}
                        {row.can.delete ? (
                            <Button
                                variant="destructive"
                                size="icon"
                                className="p-0.5"
                                type="button"
                                onClick={() => deleteRow(row)}
                                aria-label={`Dar de baja ${row.full_name}`}
                            >
                                <TrashIcon className="size-3" />
                            </Button>
                        ) : null}
                    </div>
                ),
            },
        ],
        [deleteRow],
    );

    return (
        <>
            <Head title={CONFIG_TABLEDATA.pageTitle} />

            <TabledataProvider<CustomerRow, CustomerListFilters, CustomersIndexFiltersDraftFull>
                listConfig={pickTabledataListShellConfig({
                    storageKey: tableStorageKey,
                    searchPlaceholder: CONFIG_TABLEDATA.searchPlaceholder,
                })}
                listInertia={{
                    ...CONFIG_TABLEDATA.listInertia,
                    perPageStorageKey: tableStorageKey,
                }}
                columns={columns}
                toolbar={
                    indexProps.can.create ? (
                        <FormLinkButton
                            href={customersCreate.url()}
                            icon={<CirclePlus />}
                            label="Nuevo cliente"
                            containerClassName="w-auto"
                        />
                    ) : null
                }
                emptyMessage="Ningún cliente coincide con la búsqueda."
                getRowKey={(row) => row.id}
                density="compact"
            />
        </>
    );
}

CustomersIndex.layout = {
    breadcrumbs: CONFIG_TABLEDATA.breadcrumbs.index(),
};

export default CustomersIndex;
