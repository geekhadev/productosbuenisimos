import { Head, usePage } from '@inertiajs/react';
import { CirclePlus, PencilIcon, TrashIcon } from 'lucide-react';
import { useCallback, useMemo, useState } from 'react';
import {
    pickTabledataListShellConfig,
    TabledataProvider,
} from '@/components/custom/tabledata';
import type { TabledataColumn } from '@/components/custom/tabledata';
import { Button } from '@/components/ui/button';
import type { ModulesIndexPageProps } from '@/pages/administration/modules/config';
import { CONFIG_TABLEDATA } from '@/pages/administration/modules/config';
import { ModulesIndexFilters } from '@/pages/administration/modules/filters';
import { FormDialog } from '@/pages/administration/modules/form-dialog';
import { useModulesIndex } from '@/pages/administration/modules/hooks/use-index';
import type {
    Module,
    ModuleListFilters,
    ModulesIndexFiltersDraftFull,
} from './types';

function ModulesIndex() {
    const { systems } = usePage<ModulesIndexPageProps>().props;
    const { deleteRow } = useModulesIndex();
    const [formOpen, setFormOpen] = useState(false);
    const [editingModule, setEditingModule] = useState<Module | null>(null);

    const openCreate = useCallback(() => {
        setEditingModule(null);
        setFormOpen(true);
    }, []);

    const openEdit = useCallback((row: Module) => {
        setEditingModule(row);
        setFormOpen(true);
    }, []);

    const handleFormOpenChange = useCallback((open: boolean) => {
        setFormOpen(open);

        if (!open) {
            setEditingModule(null);
        }
    }, []);

    const columns = useMemo<TabledataColumn<Module>[]>(
        () => [
            {
                key: 'system',
                label: 'Sistema',
                sortable: false,
                hideable: false,
                render: (row) => row.system?.name ?? '—',
            },
            {
                key: 'name',
                label: 'Nombre',
                sortable: true,
                hideable: false,
            },
            {
                key: 'slug',
                label: 'Slug',
                sortable: true,
                hideable: false,
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
                module={editingModule}
                systems={systems}
            />

            <TabledataProvider<Module, ModuleListFilters, ModulesIndexFiltersDraftFull>
                listConfig={pickTabledataListShellConfig(CONFIG_TABLEDATA)}
                listInertia={CONFIG_TABLEDATA.listInertia}
                columns={columns}
                toolbar={(list) => (
                    <>
                        <ModulesIndexFilters
                            filters={list.filters}
                            setFilter={list.setFilter}
                            applyFilters={list.applyFilters}
                            resetFilters={list.resetFilters}
                            systems={systems}
                        />
                        <Button type="button" onClick={openCreate}>
                            <CirclePlus />
                            Nuevo
                        </Button>
                    </>
                )}
                emptyMessage="Ningún módulo coincide con la búsqueda o el filtro."
                getRowKey={(row) => row.id}
                density="compact"
            />
        </>
    );
}

ModulesIndex.layout = {
    breadcrumbs: CONFIG_TABLEDATA.breadcrumbs.index(),
};

export default ModulesIndex;
