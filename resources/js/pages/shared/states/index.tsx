import { Head } from '@inertiajs/react';
import { CirclePlus, PencilIcon, TrashIcon } from 'lucide-react';
import { useCallback, useMemo, useState } from 'react';
import {
    pickTabledataListShellConfig,
    TabledataProvider,
} from '@/components/custom/tabledata';
import type { TabledataColumn } from '@/components/custom/tabledata';
import { Button } from '@/components/ui/button';
import { CONFIG_TABLEDATA, type StatesIndexPageProps } from '@/pages/shared/states/config';
import { StatesIndexFilters } from '@/pages/shared/states/filters';
import { FormDialog } from '@/pages/shared/states/form-dialog';
import { useStatesIndex } from '@/pages/shared/states/hooks/use-index';
import type { State, StateListFilters, StatesIndexFiltersDraftFull } from './types';

function StatesIndex({ countries }: Pick<StatesIndexPageProps, 'countries'>) {
    const { deleteRow } = useStatesIndex();
    const [formOpen, setFormOpen] = useState(false);
    const [editingState, setEditingState] = useState<State | null>(null);

    const openCreate = useCallback(() => {
        setEditingState(null);
        setFormOpen(true);
    }, []);

    const openEdit = useCallback((row: State) => {
        setEditingState(row);
        setFormOpen(true);
    }, []);

    const handleFormOpenChange = useCallback((open: boolean) => {
        setFormOpen(open);

        if (!open) {
            setEditingState(null);
        }
    }, []);

    const columns = useMemo<TabledataColumn<State>[]>(
        () => [
            {
                key: 'name',
                label: 'Nombre',
                sortable: true,
                hideable: false,
            },
            {
                key: 'country',
                label: 'Pais',
                sortable: true,
                render: (row) => row.country?.name ?? '—',
            },
            {
                key: 'actions',
                label: 'Acciones',
                sortable: false,
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
                entity={editingState}
                countries={countries}
            />

            <TabledataProvider<State, StateListFilters, StatesIndexFiltersDraftFull>
                listConfig={pickTabledataListShellConfig(CONFIG_TABLEDATA)}
                listInertia={CONFIG_TABLEDATA.listInertia}
                columns={columns}
                toolbar={(list) => (
                    <>
                        <StatesIndexFilters
                            filters={list.filters}
                            setFilter={list.setFilter}
                            applyFilters={list.applyFilters}
                            resetFilters={list.resetFilters}
                            countries={countries}
                        />
                        <Button type="button" onClick={openCreate}>
                            <CirclePlus />
                            Nuevo
                        </Button>
                    </>
                )}
                emptyMessage="Ningun estado coincide con la busqueda o los filtros."
                getRowKey={(row) => row.id}
                density="compact"
            />
        </>
    );
}

StatesIndex.layout = {
    breadcrumbs: CONFIG_TABLEDATA.breadcrumbs.index(),
};

export default StatesIndex;
