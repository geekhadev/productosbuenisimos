import { Head, Link, usePage } from '@inertiajs/react';
import { FileTextIcon, PencilIcon, SendIcon, TrashIcon } from 'lucide-react';
import { useMemo } from 'react';
import {
    pickTabledataListShellConfig,
    TabledataProvider,
} from '@/components/custom/tabledata';
import type { TabledataColumn } from '@/components/custom/tabledata';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { CONFIG_TABLEDATA } from '@/pages/sales/orders/config';
import { OrdersIndexFilters } from '@/pages/sales/orders/filters';
import { useContraEntregaSend } from '@/pages/sales/orders/hooks/use-contraentrega-send';
import { useOrdersIndex } from '@/pages/sales/orders/hooks/use-index';
import type {
    OrderListFilters,
    OrderRow,
    OrdersIndexFiltersDraftFull,
} from '@/pages/sales/orders/types';
import { edit } from '@/routes/sales/orders';

function formatDate(iso: string | null): string {
    if (iso === null || iso === '') {
        return '—';
    }

    return new Date(iso).toLocaleDateString('es-CL', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function OrdersIndex() {
    const page = usePage();
    const { deleteRow } = useOrdersIndex();
    const { selectedIds, isSending, toggleId, clearSelection, sendToContraEntrega } =
        useContraEntregaSend();

    const tableStorageKey = useMemo(() => {
        const userId = page.props.auth?.user?.id ?? 'anon';
        const companyId = page.props.company_selected?.id ?? 'none';

        return `sales-orders-${userId}-${companyId}`;
    }, [page.props.auth?.user?.id, page.props.company_selected?.id]);

    const columns = useMemo<TabledataColumn<OrderRow>[]>(
        () => [
            {
                key: 'select',
                label: '',
                hideable: false,
                headerClassName: 'w-0',
                render: (row) => (
                    <Checkbox
                        checked={selectedIds.has(row.id)}
                        onCheckedChange={() => toggleId(row.id)}
                        aria-label={`Seleccionar pedido ${row.name}`}
                    />
                ),
            },
            {
                key: 'name',
                label: 'Nombre',
                sortable: true,
                hideable: false,
            },
            {
                key: 'customer_name',
                label: 'Cliente',
                sortable: false,
                headerClassName: 'min-w-[140px]',
            },
            {
                key: 'address_summary',
                label: 'Dirección',
                sortable: false,
                headerClassName: 'min-w-[160px]',
                render: (row) => (
                    <span className="line-clamp-2 text-sm" title={row.address_summary}>
                        {row.address_summary || '—'}
                    </span>
                ),
            },
            {
                key: 'total_amount',
                label: 'Importe total',
                sortable: false,
                headerClassName: 'max-w-[80px]',
            },
            {
                key: 'items_count',
                label: 'Cant. líneas',
                sortable: false,
                headerClassName: 'max-w-[80px]',
                render: (row) => row.items_count,
            },
            {
                key: 'created_at',
                label: 'Fecha creación',
                sortable: true,
                headerClassName: 'min-w-[120px]',
                render: (row) => formatDate(row.created_at),
            },
            {
                key: 'actions',
                label: 'Acciones',
                hideable: false,
                headerClassName: 'w-0 text-right',
                render: (row) => (
                    <div className="flex justify-end gap-1">
                        {row.can.view ? (
                            <Button variant="outline" size="icon" type="button" asChild>
                                <a
                                    href={`/sales/orders/${row.id}/pdf`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label={`Descargar PDF del pedido ${row.name}`}
                                >
                                    <FileTextIcon className="size-3" />
                                </a>
                            </Button>
                        ) : null}
                        {row.can.update ? (
                            <Button variant="outline" size="icon" type="button" asChild>
                                <Link href={edit.url(row.id)} aria-label={`Editar ${row.name}`}>
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
                                aria-label={`Eliminar ${row.name}`}
                            >
                                <TrashIcon className="size-3" />
                            </Button>
                        ) : null}
                    </div>
                ),
            },
        ],
        [deleteRow, selectedIds, toggleId],
    );

    return (
        <>
            <Head title={CONFIG_TABLEDATA.pageTitle} />

            <TabledataProvider<OrderRow, OrderListFilters, OrdersIndexFiltersDraftFull>
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
                    <div className="flex flex-wrap items-center gap-2">
                        <div className="flex-1">
                            <OrdersIndexFilters
                                filters={list.filters}
                                setFilter={list.setFilter}
                                applyFilters={list.applyFilters}
                                resetFilters={list.resetFilters}
                            />
                        </div>
                        {selectedIds.size > 0 ? (
                            <Button
                                type="button"
                                variant="default"
                                size="sm"
                                className="gap-1.5 shrink-0"
                                disabled={isSending}
                                onClick={() => {
                                    void sendToContraEntrega();
                                }}
                            >
                                <SendIcon className="size-3.5" />
                                {isSending
                                    ? 'Enviando…'
                                    : `Enviar ${selectedIds.size} a ContraEntrega`}
                            </Button>
                        ) : null}
                        {selectedIds.size > 0 ? (
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                className="shrink-0 text-muted-foreground"
                                disabled={isSending}
                                onClick={clearSelection}
                            >
                                Limpiar selección ({selectedIds.size})
                            </Button>
                        ) : null}
                    </div>
                )}
                emptyMessage="Ningún pedido coincide con la búsqueda o los filtros."
                getRowKey={(row) => row.id}
                density="compact"
            />
        </>
    );
}

OrdersIndex.layout = {
    breadcrumbs: CONFIG_TABLEDATA.breadcrumbs.index(),
};

export default OrdersIndex;
