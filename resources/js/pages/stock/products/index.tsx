import { Head, Link, usePage } from '@inertiajs/react';
import { CirclePlus, PencilIcon, PowerOff, TrashIcon } from 'lucide-react';
import { useMemo } from 'react';
import { FormLinkButton } from '@/components/custom/form-link-button';
import {
    pickTabledataListShellConfig,
    TabledataProvider,
} from '@/components/custom/tabledata';
import type { TabledataColumn } from '@/components/custom/tabledata';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { CONFIG_TABLEDATA } from '@/pages/stock/products/config';
import type { ProductsIndexPageProps } from '@/pages/stock/products/config';
import { ProductsIndexFilters } from '@/pages/stock/products/filters';
import { useProductsIndex } from '@/pages/stock/products/hooks/use-index';
import type {
    ProductListFilters,
    ProductRow,
    ProductsIndexFiltersDraftFull,
} from '@/pages/stock/products/types';
import { create as productsCreate, edit } from '@/routes/stock/products';

function ProductsIndex() {
    const page = usePage();
    const indexProps = page.props as unknown as ProductsIndexPageProps;
    const { deleteRow, deactivateRow } = useProductsIndex();

    const tableStorageKey = useMemo(() => {
        const userId = page.props.auth?.user?.id ?? 'anon';
        const companyId = page.props.company_selected?.id ?? 'none';

        return `stock-products-${userId}-${companyId}`;
    }, [page.props.auth?.user?.id, page.props.company_selected?.id]);

    const columns = useMemo<TabledataColumn<ProductRow>[]>(
        () => [
            {
                key: 'name',
                label: 'Nombre',
                sortable: true,
                hideable: false,
            },
            {
                key: 'code',
                label: 'Código',
                sortable: true,
            },
            {
                key: 'sku',
                label: 'SKU',
                sortable: true,
            },
            {
                key: 'price',
                label: 'Precio',
                sortable: true,
                headerClassName: 'min-w-[100px]',
            },
            {
                key: 'width',
                label: 'Ancho',
                sortable: true,
            },
            {
                key: 'length',
                label: 'Largo',
                sortable: true,
            },
            {
                key: 'height',
                label: 'Alto',
                sortable: true,
            },
            {
                key: 'volume',
                label: 'Volumen',
                sortable: true,
            },
            {
                key: 'weight',
                label: 'Peso',
                sortable: true,
            },
            {
                key: 'minimum_stock',
                label: 'Stock mín.',
                sortable: true,
            },
            {
                key: 'is_active',
                label: 'Estado',
                sortable: true,
                headerClassName: 'min-w-[100px]',
                render: (row) =>
                    row.is_active ? (
                        <Badge variant="default">Activo</Badge>
                    ) : (
                        <Badge variant="secondary">Inactivo</Badge>
                    ),
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
                                <Link href={edit.url(row.id)} aria-label={`Editar ${row.name}`}>
                                    <PencilIcon className="size-3" />
                                </Link>
                            </Button>
                        ) : null}
                        {row.can.deactivate ? (
                            <Button
                                variant="outline"
                                size="icon"
                                type="button"
                                onClick={() => deactivateRow(row)}
                                aria-label={`Desactivar ${row.name}`}
                            >
                                <PowerOff className="size-3" />
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
        [deactivateRow, deleteRow],
    );

    return (
        <>
            <Head title={CONFIG_TABLEDATA.pageTitle} />

            <TabledataProvider<ProductRow, ProductListFilters, ProductsIndexFiltersDraftFull>
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
                        <ProductsIndexFilters
                            filters={list.filters}
                            setFilter={list.setFilter}
                            applyFilters={list.applyFilters}
                            resetFilters={list.resetFilters}
                        />
                        {indexProps.can.create ? (
                            <FormLinkButton
                                href={productsCreate.url()}
                                icon={<CirclePlus />}
                                label="Nuevo producto"
                                containerClassName="w-auto"
                            />
                        ) : null}
                    </>
                )}
                emptyMessage="Ningún producto coincide con la búsqueda o los filtros."
                getRowKey={(row) => row.id}
                density="compact"
            />
        </>
    );
}

ProductsIndex.layout = {
    breadcrumbs: CONFIG_TABLEDATA.breadcrumbs.index(),
};

export default ProductsIndex;
