import { Head, usePage } from '@inertiajs/react';
import { CirclePlus, PencilIcon, TrashIcon } from 'lucide-react';
import { useCallback, useMemo, useState } from 'react';
import {
    pickTabledataListShellConfig,
    TabledataProvider,
} from '@/components/custom/tabledata';
import type { TabledataColumn } from '@/components/custom/tabledata';
import { Button } from '@/components/ui/button';
import type { PermissionsIndexPageProps } from '@/pages/administration/permissions/config';
import { CONFIG_TABLEDATA } from '@/pages/administration/permissions/config';
import { FormDialog } from '@/pages/administration/permissions/form-dialog';
import { PermissionsIndexFilters } from '@/pages/administration/permissions/filters';
import { usePermissionsIndex } from '@/pages/administration/permissions/hooks/use-index';
import type {
    Permission,
    PermissionListFilters,
    PermissionsIndexFiltersDraftFull,
} from './types';

function PermissionsIndex() {
    const { systems, modules } = usePage<PermissionsIndexPageProps>().props;
    const { deleteRow } = usePermissionsIndex();
    const [formOpen, setFormOpen] = useState(false);
    const [editingPermission, setEditingPermission] =
        useState<Permission | null>(null);

    const openCreate = useCallback(() => {
        setEditingPermission(null);
        setFormOpen(true);
    }, []);

    const openEdit = useCallback((row: Permission) => {
        setEditingPermission(row);
        setFormOpen(true);
    }, []);

    const handleFormOpenChange = useCallback((open: boolean) => {
        setFormOpen(open);

        if (!open) {
            setEditingPermission(null);
        }
    }, []);

    const columns = useMemo<TabledataColumn<Permission>[]>(
        () => [
            {
                key: 'name',
                label: 'Nombre',
                sortable: true,
                hideable: false,
            },
            {
                key: 'module',
                label: 'Módulo',
                sortable: false,
                hideable: false,
                render: (row) => row.module?.name ?? '—',
            },
            {
                key: 'system',
                label: 'Sistema',
                sortable: false,
                hideable: false,
                render: (row) => row.module?.system?.name ?? '—',
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
                permission={editingPermission}
                systems={systems}
                modules={modules}
            />

            <TabledataProvider<
                Permission,
                PermissionListFilters,
                PermissionsIndexFiltersDraftFull
            >
                listConfig={pickTabledataListShellConfig(CONFIG_TABLEDATA)}
                listInertia={CONFIG_TABLEDATA.listInertia}
                columns={columns}
                toolbar={(list) => (
                    <>
                        <PermissionsIndexFilters
                            filters={list.filters}
                            setFilter={list.setFilter}
                            applyFilters={list.applyFilters}
                            resetFilters={list.resetFilters}
                            systems={systems}
                            modules={modules}
                        />
                        <Button type="button" onClick={openCreate}>
                            <CirclePlus />
                            Nuevo
                        </Button>
                    </>
                )}
                emptyMessage="Ningún permiso coincide con la búsqueda o los filtros."
                getRowKey={(row) => row.id}
                density="compact"
            />
        </>
    );
}

PermissionsIndex.layout = {
    breadcrumbs: CONFIG_TABLEDATA.breadcrumbs.index(),
};

export default PermissionsIndex;
