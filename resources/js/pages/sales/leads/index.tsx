import { Head, Link, usePage } from '@inertiajs/react';
import { CirclePlus, PencilIcon, TrashIcon } from 'lucide-react';
import { useMemo } from 'react';
import { FormLinkButton } from '@/components/custom/form-link-button';
import {
    pickTabledataListShellConfig,
    TabledataProvider,
} from '@/components/custom/tabledata';
import type { TabledataColumn } from '@/components/custom/tabledata';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { CONFIG_TABLEDATA } from '@/pages/sales/leads/config';
import type { LeadsIndexPageProps } from '@/pages/sales/leads/config';
import { LeadsIndexFilters } from '@/pages/sales/leads/filters';
import { useLeadsIndex } from '@/pages/sales/leads/hooks/use-index';
import {
    LEAD_SOURCE_LABELS,
    LEAD_STATUS_LABELS,
} from '@/pages/sales/leads/labels';
import type {
    LeadListFilters,
    LeadRow,
    LeadsIndexFiltersDraftFull,
} from '@/pages/sales/leads/types';
import { create as leadsCreate, edit } from '@/routes/sales/leads';

function statusBadgeVariant(status: string): 'default' | 'secondary' | 'outline' {
    switch (status) {
        case 'convertido':
            return 'default';
        case 'inactivo':
            return 'secondary';
        default:
            return 'outline';
    }
}

function LeadsIndex() {
    const page = usePage();
    const indexProps = page.props as unknown as LeadsIndexPageProps;
    const { deleteRow } = useLeadsIndex();

    const tableStorageKey = useMemo(() => {
        const userId = page.props.auth?.user?.id ?? 'anon';
        const companyId = page.props.company_selected?.id ?? 'none';

        return `sales-leads-${userId}-${companyId}`;
    }, [page.props.auth?.user?.id, page.props.company_selected?.id]);

    const columns = useMemo<TabledataColumn<LeadRow>[]>(
        () => [
            {
                key: 'phone',
                label: 'Teléfono',
                sortable: true,
                hideable: false,
                headerClassName: 'min-w-[120px]',
            },
            {
                key: 'source',
                label: 'Fuente',
                sortable: true,
                render: (row) =>
                    LEAD_SOURCE_LABELS[row.source] ?? row.source,
            },
            {
                key: 'status',
                label: 'Estado',
                sortable: true,
                render: (row) => (
                    <Badge variant={statusBadgeVariant(row.status)}>
                        {LEAD_STATUS_LABELS[row.status] ?? row.status}
                    </Badge>
                ),
            },
            {
                key: 'customer_name',
                label: 'Cliente',
                render: (row) => row.customer_name ?? '—',
            },
            {
                key: 'created_at',
                label: 'Creado',
                sortable: true,
                headerClassName: 'min-w-[140px]',
                render: (row) =>
                    row.created_at != null
                        ? new Date(row.created_at).toLocaleString()
                        : '—',
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
                                    aria-label={`Editar lead ${row.phone}`}
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
                                aria-label={`Dar de baja lead ${row.phone}`}
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

            <TabledataProvider<LeadRow, LeadListFilters, LeadsIndexFiltersDraftFull>
                listConfig={pickTabledataListShellConfig({
                    storageKey: tableStorageKey,
                    searchPlaceholder: CONFIG_TABLEDATA.searchPlaceholder,
                })}
                listInertia={{
                    ...CONFIG_TABLEDATA.listInertia,
                    perPageStorageKey: tableStorageKey,
                }}
                columns={columns}
                toolbar={(list) => (
                    <>
                        <LeadsIndexFilters
                            filters={list.filters}
                            setFilter={list.setFilter}
                            applyFilters={list.applyFilters}
                            resetFilters={list.resetFilters}
                        />
                        {indexProps.can.create ? (
                            <FormLinkButton
                                href={leadsCreate.url()}
                                icon={<CirclePlus />}
                                label="Nuevo lead"
                                containerClassName="w-auto"
                            />
                        ) : null}
                    </>
                )}
                emptyMessage="Ningún lead coincide con la búsqueda."
                getRowKey={(row) => row.id}
                density="compact"
            />
        </>
    );
}

LeadsIndex.layout = {
    breadcrumbs: CONFIG_TABLEDATA.breadcrumbs.index(),
};

export default LeadsIndex;
