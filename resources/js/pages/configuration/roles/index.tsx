import { Head, router, usePage } from '@inertiajs/react';
import { ChevronRight, Columns3, Trash2 } from 'lucide-react';
import {
    useCallback,
    useEffect,
    useLayoutEffect,
    useMemo,
    useRef,
    useState,
} from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import {
    flattenPermissionsTree,
    permissionIdsForModule,
    permissionIdsForSystem,
} from '@/pages/configuration/roles/types';
import type {
    MatrixRole,
    PermissionTreeSystem,
    RolesMatrixPageProps,
} from '@/pages/configuration/roles/types';
import { dashboard } from '@/routes';
import { destroy, index as rolesIndex, store, sync } from '@/routes/configuration/roles';

const AUTOSAVE_MS = 280;

function cloneRoles(roles: MatrixRole[]): MatrixRole[] {
    return roles.map((r) => ({
        ...r,
        permission_ids: [...new Set(r.permission_ids)],
    }));
}

function serializeRoles(roles: MatrixRole[]): string {
    return JSON.stringify(
        roles.map((r) => ({
            id: r.id,
            name: r.name,
            permission_ids: [...r.permission_ids].sort((a, b) =>
                a.localeCompare(b),
            ),
        })),
    );
}

function moduleKeysFromTree(tree: PermissionTreeSystem[]): string[] {
    return tree.flatMap((system) =>
        system.modules.map((mod) => `m-${mod.id}`),
    );
}

/** Estado visual del checkbox masivo (todo / nada / parcial). */
function bulkCheckedState(
    ids: string[],
    rolePermissionIds: string[],
): boolean | 'indeterminate' {
    if (ids.length === 0) {
        return false;
    }

    const n = ids.filter((id) => rolePermissionIds.includes(id)).length;

    if (n === 0) {
        return false;
    }

    if (n === ids.length) {
        return true;
    }

    return 'indeterminate';
}

/** Fondo blanco en la matriz (sustituye el relleno primary del componente base). */
const rolesMatrixCheckboxClassName =
    'size-4 bg-white shadow-xs dark:bg-zinc-950 data-[state=checked]:border-primary data-[state=checked]:bg-white data-[state=checked]:text-primary data-[state=indeterminate]:border-primary/70 data-[state=indeterminate]:bg-white data-[state=indeterminate]:text-primary';

/** Altura fija de filas del cuerpo (el `min-h` del viewport va en el wrapper, no en la tabla). */
const matrixBodyRowClassName = 'h-9 max-h-9';

function RolesMatrixPage() {
    const { permissionsTree, roles: rolesFromServer, companyMissing } =
        usePage<RolesMatrixPageProps>().props;

    const [rolesMatrix, setRolesMatrix] = useState<MatrixRole[]>(() =>
        cloneRoles(rolesFromServer),
    );
    const [draftRoleName, setDraftRoleName] = useState('');
    const [selectedColumnId, setSelectedColumnId] = useState<string | null>(
        null,
    );
    const [collapsedModuleKeys, setCollapsedModuleKeys] = useState<string[]>(
        () => moduleKeysFromTree(permissionsTree),
    );
    const [collapsedSystemKeys, setCollapsedSystemKeys] = useState<string[]>(
        [],
    );
    const [hiddenRoleColumnIds, setHiddenRoleColumnIds] = useState<string[]>(
        [],
    );

    const rolesMatrixRef = useRef(rolesMatrix);
    const lastSyncedSerializedRef = useRef(
        serializeRoles(cloneRoles(rolesFromServer)),
    );
    const lastServerRolesSerializedRef = useRef(
        serializeRoles(cloneRoles(rolesFromServer)),
    );
    const autosaveTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const draftCommitInFlightRef = useRef(false);

    useLayoutEffect(() => {
        rolesMatrixRef.current = rolesMatrix;
    }, [rolesMatrix]);

    useEffect(() => {
        const incoming = cloneRoles(rolesFromServer);
        const incomingSerialized = serializeRoles(incoming);

        if (incomingSerialized === lastServerRolesSerializedRef.current) {
            return;
        }

        lastServerRolesSerializedRef.current = incomingSerialized;
        setRolesMatrix(incoming);
        lastSyncedSerializedRef.current = incomingSerialized;
    }, [rolesFromServer]);

    useEffect(() => {
        setCollapsedModuleKeys(moduleKeysFromTree(permissionsTree));
    }, [permissionsTree]);

    useEffect(() => {
        const validIds = new Set(rolesFromServer.map((r) => r.id));
        setHiddenRoleColumnIds((prev) => prev.filter((id) => validIds.has(id)));
    }, [rolesFromServer]);

    useEffect(() => {
        if (companyMissing) {
            return;
        }

        if (rolesFromServer.length === 0) {
            setSelectedColumnId(null);

            return;
        }

        setSelectedColumnId((prev) => {
            if (prev !== null && rolesFromServer.some((r) => r.id === prev)) {
                return prev;
            }

            return rolesFromServer[0]?.id ?? null;
        });
    }, [rolesFromServer, companyMissing]);

    useEffect(() => {
        if (companyMissing) {
            return;
        }

        if (rolesMatrix.length === 0) {
            return;
        }

        const serialized = serializeRoles(rolesMatrix);

        if (serialized === lastSyncedSerializedRef.current) {
            return;
        }

        if (autosaveTimerRef.current !== null) {
            clearTimeout(autosaveTimerRef.current);
        }

        autosaveTimerRef.current = setTimeout(() => {
            autosaveTimerRef.current = null;
            const payload = cloneRoles(rolesMatrixRef.current);

            router.put(
                sync.url(),
                { roles: payload },
                {
                    preserveScroll: true,
                    only: ['roles', 'permissionsTree', 'canEditPublicRoles'],
                    onSuccess: () => {
                        lastSyncedSerializedRef.current =
                            serializeRoles(payload);
                    },
                },
            );
        }, AUTOSAVE_MS);

        return () => {
            if (autosaveTimerRef.current !== null) {
                clearTimeout(autosaveTimerRef.current);
                autosaveTimerRef.current = null;
            }
        };
    }, [rolesMatrix, companyMissing]);

    const flatRows = useMemo(
        () => flattenPermissionsTree(permissionsTree),
        [permissionsTree],
    );

    const visibleRoleColumns = useMemo(
        () =>
            rolesMatrix
                .map((role, matrixIndex) => ({ role, matrixIndex }))
                .filter(({ role }) => !hiddenRoleColumnIds.includes(role.id)),
        [rolesMatrix, hiddenRoleColumnIds],
    );

    const toggleModuleCollapse = useCallback((moduleKey: string) => {
        setCollapsedModuleKeys((prev) =>
            prev.includes(moduleKey)
                ? prev.filter((k) => k !== moduleKey)
                : [...prev, moduleKey],
        );
    }, []);

    const toggleSystemCollapse = useCallback((systemKey: string) => {
        setCollapsedSystemKeys((prev) =>
            prev.includes(systemKey)
                ? prev.filter((k) => k !== systemKey)
                : [...prev, systemKey],
        );
    }, []);

    const visibleRows = useMemo(() => {
        const collapsedModules = new Set(collapsedModuleKeys);
        const collapsedSystems = new Set(collapsedSystemKeys);

        return flatRows.filter((row) => {
            if (row.kind === 'system') {
                return true;
            }

            if (collapsedSystems.has(row.systemKey)) {
                return false;
            }

            if (row.kind === 'permission') {
                return !collapsedModules.has(row.moduleKey);
            }

            return true;
        });
    }, [flatRows, collapsedModuleKeys, collapsedSystemKeys]);

    const applyBulkToRole = useCallback(
        (roleIndex: number, ids: string[], on: boolean) => {
            if (ids.length === 0 || roleIndex < 0) {
                return;
            }

            setRolesMatrix((prev) => {
                const next = cloneRoles(prev);
                const role = next[roleIndex];

                if (!role) {
                    return prev;
                }

                const set = new Set(role.permission_ids);

                if (on) {
                    ids.forEach((id) => set.add(id));
                } else {
                    ids.forEach((id) => set.delete(id));
                }

                role.permission_ids = [...set];

                return next;
            });
        },
        [],
    );

    const onMatrixBulkCheckedChange = useCallback(
        (
            matrixIndex: number,
            permissionIds: string[],
            value: boolean | 'indeterminate',
        ) => {
            if (permissionIds.length === 0) {
                return;
            }

            if (value === 'indeterminate') {
                applyBulkToRole(matrixIndex, permissionIds, true);

                return;
            }

            applyBulkToRole(matrixIndex, permissionIds, value === true);
        },
        [applyBulkToRole],
    );

    const permissionStripeByKey = useMemo(() => {
        const map = new Map<string, boolean>();
        let n = 0;

        for (const row of visibleRows) {
            if (row.kind === 'permission') {
                map.set(row.key, n % 2 === 0);
                n += 1;
            }
        }

        return map;
    }, [visibleRows]);

    const setRoleName = useCallback((index: number, name: string) => {
        setRolesMatrix((prev) => {
            const next = cloneRoles(prev);
            const row = next[index];

            if (row) {
                row.name = name;
            }

            return next;
        });
    }, []);

    const toggleCell = useCallback(
        (roleIndex: number, permissionId: string, on: boolean) => {
            setRolesMatrix((prev) => {
                const next = cloneRoles(prev);
                const role = next[roleIndex];

                if (!role) {
                    return prev;
                }

                role.permission_ids = on
                    ? [...new Set([...role.permission_ids, permissionId])]
                    : role.permission_ids.filter((id) => id !== permissionId);

                return next;
            });
        },
        [],
    );

    const commitDraftRole = useCallback(() => {
        if (draftCommitInFlightRef.current) {
            return;
        }

        const name = draftRoleName.trim();

        if (name === '') {
            return;
        }

        draftCommitInFlightRef.current = true;

        router.post(
            store.url(),
            {
                name,
                permission_ids: [],
            },
            {
                preserveScroll: true,
                only: ['roles', 'permissionsTree', 'canEditPublicRoles'],
                onSuccess: () => setDraftRoleName(''),
                onFinish: () => {
                    draftCommitInFlightRef.current = false;
                },
            },
        );
    }, [draftRoleName]);

    const removeRoleColumn = useCallback((role: MatrixRole) => {
        const message = role.is_public
            ? `¿Eliminar el rol global «${role.name}»? Esto afecta a todas las empresas.`
            : `¿Eliminar el rol «${role.name}»?`;

        if (!window.confirm(message)) {
            return;
        }

        router.delete(destroy.url(role.id), {
            preserveScroll: true,
            only: ['roles', 'permissionsTree', 'canEditPublicRoles'],
        });
    }, []);

    const draftHeader = (
        <th
            key="draft-role-column"
            className="sticky top-0 z-20 w-28 min-w-28 bg-muted/95 px-1 py-1 backdrop-blur-sm"
        >
            <div className="overflow-hidden rounded-md border border-input bg-white shadow-xs dark:border-input dark:bg-zinc-950">
                <Input
                    value={draftRoleName}
                    onChange={(e) => setDraftRoleName(e.target.value)}
                    onBlur={commitDraftRole}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            commitDraftRole();
                        }
                    }}
                    placeholder="Nuevo rol"
                    className="h-7 rounded-none border-0 bg-white px-2 text-center text-[10px] leading-tight shadow-none focus-visible:ring-0 focus-visible:ring-offset-0 dark:bg-zinc-950"
                    aria-label="Nombre del nuevo rol"
                />
            </div>
        </th>
    );

    const draftBodyCell = (className: string) => (
        <td
            key="draft-role-column"
            className={cn(
                'w-28 min-w-28 px-1 py-0 align-middle',
                className,
            )}
            aria-hidden
        />
    );

    if (companyMissing) {
        return (
            <>
                <Head title="Roles" />
                <div className="p-6">
                    <Heading
                        variant="small"
                        title="Roles"
                        description="Tu usuario no tiene una empresa asignada. Asigna una empresa para gestionar roles."
                    />
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Roles" />

            <div className="flex w-full flex-col gap-4 p-4">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <Heading
                        variant="small"
                        title="Roles y permisos"
                        description="Aquí puedes revisar y ajustar la configuración. Los cambios se guardan automáticamente."
                    />
                    {rolesMatrix.length > 0 ? (
                        <Popover>
                            <PopoverTrigger asChild>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    className="shrink-0 gap-2 self-start"
                                >
                                    <Columns3 className="size-4" />
                                    Ocultar roles
                                    {hiddenRoleColumnIds.length > 0 ? (
                                        <span className="rounded-full bg-muted px-1.5 py-0.5 text-[10px] tabular-nums text-muted-foreground">
                                            {
                                                visibleRoleColumns.length
                                            }/{rolesMatrix.length}
                                        </span>
                                    ) : null}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent
                                align="end"
                                className="w-72 p-3"
                            >
                                <p className="text-sm font-medium">
                                    Visibilidad de roles
                                </p>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    Oculta roles en pantalla cuando haya muchos
                                    roles; los datos no se pierden.
                                </p>
                                <div className="mt-3 max-h-56 space-y-1 overflow-y-auto pr-1">
                                    {rolesMatrix.map((role) => (
                                        <label
                                            key={role.id}
                                            className="flex cursor-pointer items-center gap-2 rounded-md px-1 py-1.5 hover:bg-muted/80"
                                        >
                                            <Checkbox
                                                checked={
                                                    !hiddenRoleColumnIds.includes(
                                                        role.id,
                                                    )
                                                }
                                                onCheckedChange={(v) => {
                                                    if (
                                                        v === 'indeterminate'
                                                    ) {
                                                        return;
                                                    }

                                                    setHiddenRoleColumnIds(
                                                        (prev) =>
                                                            v === true
                                                                ? prev.filter(
                                                                      (id) =>
                                                                          id !==
                                                                          role.id,
                                                                  )
                                                                : [
                                                                      ...prev,
                                                                      role.id,
                                                                  ],
                                                    );
                                                }}
                                                aria-label={`Mostrar columna del rol ${role.name}`}
                                            />
                                            <span className="min-w-0 flex-1 truncate text-sm">
                                                {role.name}
                                            </span>
                                        </label>
                                    ))}
                                </div>
                                <div className="mt-3 flex flex-wrap gap-2 border-t border-border pt-3">
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        size="sm"
                                        className="text-xs"
                                        onClick={() =>
                                            setHiddenRoleColumnIds([])
                                        }
                                    >
                                        Mostrar todas
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        className="text-xs"
                                        onClick={() =>
                                            setHiddenRoleColumnIds(
                                                rolesMatrix.map((r) => r.id),
                                            )
                                        }
                                    >
                                        Ocultar todas
                                    </Button>
                                </div>
                            </PopoverContent>
                        </Popover>
                    ) : null}
                </div>

                <div className="relative min-h-[calc(100vh-200px)] overflow-x-auto rounded-lg">
                    <table className="h-auto w-full min-w-max border-collapse text-xs">
                        <thead>
                            <tr className="bg-muted/40">
                                <th
                                    className={cn(
                                        'sticky left-0 z-30 min-w-44 max-w-xs bg-muted/95 px-2 py-1 text-left text-[11px] font-semibold backdrop-blur-sm',
                                        'top-0',
                                    )}
                                >
                                    Permiso
                                </th>
                                {visibleRoleColumns.map(
                                    ({ role, matrixIndex }) => (
                                    <th
                                        key={role.id}
                                        className={cn(
                                            'sticky top-0 z-20 min-w-36 bg-muted/95 px-1 py-1 font-medium backdrop-blur-sm',
                                            selectedColumnId === role.id &&
                                                'bg-primary/10 ring-2 ring-primary/40 ring-inset',
                                        )}
                                    >
                                        <div className="flex w-full min-w-0 items-stretch overflow-hidden rounded-md border border-input bg-white shadow-xs dark:border-input dark:bg-zinc-950">
                                            <Input
                                                value={role.name}
                                                onChange={(e) =>
                                                    setRoleName(
                                                        matrixIndex,
                                                        e.target.value,
                                                    )
                                                }
                                                onFocus={() =>
                                                    setSelectedColumnId(role.id)
                                                }
                                                className="h-7 min-w-0 flex-1 rounded-none border-0 bg-white px-1.5 text-center !text-xs font-semibold leading-tight shadow-none focus-visible:ring-0 focus-visible:ring-offset-0 dark:bg-zinc-950"
                                                aria-label={`Nombre del rol columna ${matrixIndex + 1}`}
                                            />
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                className="h-7 w-7 shrink-0 rounded-none border-l border-input text-destructive hover:bg-destructive/10 hover:text-destructive dark:border-input"
                                                onClick={() =>
                                                    removeRoleColumn(role)
                                                }
                                                aria-label={`Eliminar rol ${role.name}`}
                                            >
                                                <Trash2 className="size-3" />
                                            </Button>
                                        </div>
                                    </th>
                                ))}
                                {draftHeader}
                            </tr>
                        </thead>
                        <tbody>
                            {visibleRows.map((row) => {
                                if (row.kind === 'system') {
                                    const systemCollapsed =
                                        collapsedSystemKeys.includes(row.key);
                                    const systemPermIds = permissionIdsForSystem(
                                        permissionsTree,
                                        row.systemId,
                                    );

                                    const systemRowBg =
                                        'border-b border-blue-200/80 bg-blue-100 dark:border-blue-800/80 dark:bg-blue-900/45';
                                    const systemFirstTd =
                                        'sticky left-0 z-20 h-9 max-h-9 cursor-pointer select-none border-r border-blue-200/80 bg-blue-100 px-2 py-0 align-middle backdrop-blur-sm outline-none hover:bg-blue-200/70 focus-visible:ring-2 focus-visible:ring-ring dark:border-blue-800/60 dark:bg-blue-900/45 dark:hover:bg-blue-800/55';
                                    const systemRoleTd =
                                        'h-9 max-h-9 bg-blue-100/95 px-1 py-0 text-center align-middle dark:border-blue-800/50 dark:bg-blue-900/40';
                                    const systemDraftTd =
                                        'h-9 max-h-9 bg-blue-100/95 py-0 dark:border-blue-800/50 dark:bg-blue-900/40';

                                    return (
                                        <tr
                                            key={row.key}
                                            className={cn(
                                                systemRowBg,
                                                matrixBodyRowClassName,
                                            )}
                                        >
                                            <td
                                                role="button"
                                                tabIndex={0}
                                                aria-expanded={!systemCollapsed}
                                                aria-label={`${systemCollapsed ? 'Expandir' : 'Contraer'} sistema ${row.label}`}
                                                className={systemFirstTd}
                                                onClick={() =>
                                                    toggleSystemCollapse(row.key)
                                                }
                                                onKeyDown={(e) => {
                                                    if (
                                                        e.key === 'Enter' ||
                                                        e.key === ' '
                                                    ) {
                                                        e.preventDefault();
                                                        toggleSystemCollapse(
                                                            row.key,
                                                        );
                                                    }
                                                }}
                                            >
                                                <div className="flex h-full min-h-0 w-full min-w-0 items-center gap-1 text-left">
                                                    <ChevronRight
                                                        className={cn(
                                                            'size-3 shrink-0 text-muted-foreground transition-transform duration-200',
                                                            !systemCollapsed &&
                                                                'rotate-90',
                                                        )}
                                                        aria-hidden
                                                    />
                                                    <span className="min-w-0 truncate text-[10px] font-bold uppercase leading-tight tracking-wide text-muted-foreground">
                                                        {row.label}
                                                    </span>
                                                </div>
                                            </td>
                                            {visibleRoleColumns.map(
                                                ({
                                                    role,
                                                    matrixIndex,
                                                }) => {
                                                const systemBulkState =
                                                    bulkCheckedState(
                                                        systemPermIds,
                                                        role.permission_ids,
                                                    );

                                                return (
                                                    <td
                                                        key={`${row.key}-r-${role.id}`}
                                                        className={cn(
                                                            systemRoleTd,
                                                            selectedColumnId ===
                                                                role.id &&
                                                                'bg-primary/10',
                                                        )}
                                                    >
                                                        <div className="flex h-full items-center justify-center">
                                                            <Checkbox
                                                                disabled={
                                                                    systemPermIds.length ===
                                                                    0
                                                                }
                                                                checked={
                                                                    systemBulkState
                                                                }
                                                                onCheckedChange={(
                                                                    v,
                                                                ) =>
                                                                    onMatrixBulkCheckedChange(
                                                                        matrixIndex,
                                                                        systemPermIds,
                                                                        v,
                                                                    )
                                                                }
                                                                className={
                                                                    rolesMatrixCheckboxClassName
                                                                }
                                                                aria-label={`Sistema ${row.label}: todos los permisos para ${role.name}`}
                                                            />
                                                        </div>
                                                    </td>
                                                );
                                            })}
                                            {draftBodyCell(systemDraftTd)}
                                        </tr>
                                    );
                                }

                                if (row.kind === 'module') {
                                    const moduleCollapsed =
                                        collapsedModuleKeys.includes(row.key);
                                    const modulePermIds = permissionIdsForModule(
                                        permissionsTree,
                                        row.moduleId,
                                    );

                                    const moduleRowBg =
                                        'border-b border-blue-200/70 bg-blue-50 dark:border-blue-800/80 dark:bg-blue-950/45';
                                    const moduleFirstTd =
                                        'sticky left-0 z-20 h-9 max-h-9 cursor-pointer select-none bg-blue-50 px-2 py-0 pl-2 align-middle backdrop-blur-sm outline-none hover:bg-blue-100/80 focus-visible:ring-2 focus-visible:ring-ring dark:bg-blue-950/45 dark:hover:bg-blue-900/60';
                                    const moduleRoleTd =
                                        'h-9 max-h-9 bg-blue-50/90 px-1 py-0 text-center align-middle dark:bg-blue-950/40';
                                    const moduleDraftTd =
                                        'h-9 max-h-9 bg-blue-50/90 py-0 dark:bg-blue-950/40';

                                    return (
                                        <tr
                                            key={row.key}
                                            className={cn(
                                                moduleRowBg,
                                                matrixBodyRowClassName,
                                            )}
                                        >
                                            <td
                                                role="button"
                                                tabIndex={0}
                                                aria-expanded={!moduleCollapsed}
                                                aria-label={`${moduleCollapsed ? 'Expandir' : 'Contraer'} módulo ${row.label}`}
                                                className={moduleFirstTd}
                                                onClick={() =>
                                                    toggleModuleCollapse(
                                                        row.key,
                                                    )
                                                }
                                                onKeyDown={(e) => {
                                                    if (
                                                        e.key === 'Enter' ||
                                                        e.key === ' '
                                                    ) {
                                                        e.preventDefault();
                                                        toggleModuleCollapse(
                                                            row.key,
                                                        );
                                                    }
                                                }}
                                            >
                                                <div className="flex h-full min-h-0 w-full min-w-0 items-center gap-1 text-left">
                                                    <ChevronRight
                                                        className={cn(
                                                            'size-3 shrink-0 text-muted-foreground transition-transform duration-200',
                                                            !moduleCollapsed &&
                                                                'rotate-90',
                                                        )}
                                                        aria-hidden
                                                    />
                                                    <span className="min-w-0 truncate text-xs font-semibold leading-tight text-foreground">
                                                        {row.label}
                                                    </span>
                                                </div>
                                            </td>
                                            {visibleRoleColumns.map(
                                                ({
                                                    role,
                                                    matrixIndex,
                                                }) => {
                                                const moduleBulkState =
                                                    bulkCheckedState(
                                                        modulePermIds,
                                                        role.permission_ids,
                                                    );

                                                return (
                                                    <td
                                                        key={`${row.key}-r-${role.id}`}
                                                        className={cn(
                                                            moduleRoleTd,
                                                            selectedColumnId ===
                                                                role.id &&
                                                                'bg-primary/10',
                                                        )}
                                                    >
                                                        <div className="flex h-full items-center justify-center">
                                                            <Checkbox
                                                                disabled={
                                                                    modulePermIds.length ===
                                                                    0
                                                                }
                                                                checked={
                                                                    moduleBulkState
                                                                }
                                                                onCheckedChange={(
                                                                    v,
                                                                ) =>
                                                                    onMatrixBulkCheckedChange(
                                                                        matrixIndex,
                                                                        modulePermIds,
                                                                        v,
                                                                    )
                                                                }
                                                                className={
                                                                    rolesMatrixCheckboxClassName
                                                                }
                                                                aria-label={`Módulo ${row.label}: todos los permisos para ${role.name}`}
                                                            />
                                                        </div>
                                                    </td>
                                                );
                                            })}
                                            {draftBodyCell(moduleDraftTd)}
                                        </tr>
                                    );
                                }

                                const zebra =
                                    permissionStripeByKey.get(row.key) ?? true;

                                return (
                                    <tr
                                        key={row.key}
                                        className={cn(
                                            'border-b border-border',
                                            matrixBodyRowClassName,
                                            zebra
                                                ? 'bg-background'
                                                : 'bg-muted/25',
                                        )}
                                    >
                                        <td
                                            className={cn(
                                                'sticky left-0 z-20 h-9 max-h-9 max-w-xs border-r border-border py-0 pl-8 pr-2 align-middle backdrop-blur-sm',
                                                zebra
                                                    ? 'bg-background'
                                                    : 'bg-muted/25',
                                            )}
                                        >
                                            <span className="block min-w-0 truncate text-xs font-medium leading-tight text-foreground">
                                                {row.label}
                                            </span>
                                        </td>
                                        {visibleRoleColumns.map(
                                            ({ role, matrixIndex }) => (
                                            <td
                                                key={`${role.id}-${row.id}`}
                                                className={cn(
                                                    'h-9 max-h-9 px-1 py-0 text-center align-middle',
                                                    selectedColumnId ===
                                                        role.id && 'bg-primary/5',
                                                    zebra
                                                        ? 'bg-background'
                                                        : 'bg-muted/25',
                                                )}
                                            >
                                                <div className="flex h-full items-center justify-center">
                                                    <Checkbox
                                                        checked={role.permission_ids.includes(
                                                            row.id,
                                                        )}
                                                        onCheckedChange={(v) => {
                                                            if (
                                                                v ===
                                                                'indeterminate'
                                                            ) {
                                                                return;
                                                            }

                                                            toggleCell(
                                                                matrixIndex,
                                                                row.id,
                                                                v === true,
                                                            );
                                                        }}
                                                        className={
                                                            rolesMatrixCheckboxClassName
                                                        }
                                                        aria-label={`${role.name}: ${row.label}`}
                                                    />
                                                </div>
                                            </td>
                                        ))}
                                        {draftBodyCell(
                                            cn(
                                                'h-9 max-h-9 border-l border-border',
                                                zebra
                                                    ? 'bg-background'
                                                    : 'bg-muted/25',
                                            ),
                                        )}
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

RolesMatrixPage.layout = {
    breadcrumbs: [
        { title: 'Panel', href: dashboard() },
        { title: 'Roles', href: rolesIndex() },
    ],
};

export default RolesMatrixPage;
