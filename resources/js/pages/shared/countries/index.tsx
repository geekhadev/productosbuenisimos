import { Head } from '@inertiajs/react';
import { CirclePlus, PencilIcon, TrashIcon } from 'lucide-react';
import { useCallback, useMemo, useState } from 'react';
import {
    pickTabledataListShellConfig,
    TabledataProvider,
} from '@/components/custom/tabledata';
import type { TabledataColumn } from '@/components/custom/tabledata';
import { Button } from '@/components/ui/button';
import { CONFIG_TABLEDATA } from '@/pages/shared/countries/config';
import { FormDialog } from '@/pages/shared/countries/form-dialog';
import { useCountriesIndex } from '@/pages/shared/countries/hooks/use-index';
import type {
    CountriesIndexFiltersDraftFull,
    Country,
    CountryListFilters,
} from './types';

function CountriesIndex() {
    const { deleteRow } = useCountriesIndex();
    const [formOpen, setFormOpen] = useState(false);
    const [editingCountry, setEditingCountry] = useState<Country | null>(null);

    const openCreate = useCallback(() => {
        setEditingCountry(null);
        setFormOpen(true);
    }, []);

    const openEdit = useCallback((row: Country) => {
        setEditingCountry(row);
        setFormOpen(true);
    }, []);

    const handleFormOpenChange = useCallback((open: boolean) => {
        setFormOpen(open);

        if (!open) {
            setEditingCountry(null);
        }
    }, []);

    const columns = useMemo<TabledataColumn<Country>[]>(
        () => [
            {
                key: 'name',
                label: 'Nombre',
                sortable: true,
                hideable: false,
            },
            {
                key: 'name_code',
                label: 'Código',
                sortable: true,
                headerClassName: 'min-w-[80px]',
            },
            {
                key: 'phone_code',
                label: 'Teléfono',
                sortable: true,
                headerClassName: 'min-w-[100px]',
            },
            {
                key: 'currency_name',
                label: 'Moneda',
                sortable: true,
                headerClassName: 'min-w-[140px]',
            },
            {
                key: 'currency_symbol',
                label: 'Símbolo',
                sortable: true,
                headerClassName: 'min-w-[100px]',
            },
            {
                key: 'actions',
                label: 'Acciones',
                hideable: false,
                headerClassName: 'w-0 text-right',
                render: (row) => (
                    <div className="flex justify-end gap-1">
                        <Button
                            variant="outline"
                            size="icon"
                            type="button"
                            onClick={() => openEdit(row)}
                        >
                            <PencilIcon className="size-3" />
                        </Button>
                        <Button
                            variant="destructive"
                            size="icon"
                            className="p-0.5"
                            type="button"
                            onClick={() => deleteRow(row)}
                        >
                            <TrashIcon className="size-3" />
                        </Button>
                    </div>
                ),
            },
        ],
        [deleteRow, openEdit],
    );

    return (
        <>
            <Head title={CONFIG_TABLEDATA.pageTitle} />

            <FormDialog
                open={formOpen}
                onOpenChange={handleFormOpenChange}
                country={editingCountry}
            />

            <TabledataProvider<
                Country,
                CountryListFilters,
                CountriesIndexFiltersDraftFull
            >
                listConfig={pickTabledataListShellConfig(CONFIG_TABLEDATA)}
                listInertia={CONFIG_TABLEDATA.listInertia}
                columns={columns}
                toolbar={
                    <Button type="button" onClick={openCreate}>
                        <CirclePlus />
                        Nuevo
                    </Button>
                }
                emptyMessage="Ningún país coincide con la búsqueda."
                getRowKey={(row) => row.id}
                density="compact"
            />
        </>
    );
}

CountriesIndex.layout = {
    breadcrumbs: CONFIG_TABLEDATA.breadcrumbs.index(),
};

export default CountriesIndex;
