import { router } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { toast } from 'sonner';
import type { FormSelectOption } from '@/components/custom/form-select';
import { TABLEDATA_LIST_INERTIA_ONLY } from '@/components/custom/tabledata';
import { jsonFetchHeaders } from '@/lib/json-fetch-headers';
import {
    companyAssignableRolesUrl,
    userCompanyRoleAssignmentUrl,
    userCompanyRoleAssignmentsUrl,
} from '@/pages/configuration/users/company-roles-url';
import type {
    CompanyRoleAssignmentRow,
    CompanyRolesDialogProps,
    RoleOption,
} from '@/pages/configuration/users/company-roles-dialog.types';

type UseCompanyRolesDialogParams = Pick<
    CompanyRolesDialogProps,
    'open' | 'user' | 'listMode' | 'companies' | 'sessionCompanyId'
>;

export function useCompanyRolesDialog({
    open,
    user,
    listMode,
    companies,
    sessionCompanyId,
}: UseCompanyRolesDialogParams) {
    const [companyId, setCompanyId] = useState('');
    const [roleId, setRoleId] = useState('');
    const [roleOptions, setRoleOptions] = useState<RoleOption[]>([]);
    const [assignments, setAssignments] = useState<CompanyRoleAssignmentRow[]>(
        [],
    );
    const [loadingRoles, setLoadingRoles] = useState(false);
    const [loadingAssignments, setLoadingAssignments] = useState(false);
    const [adding, setAdding] = useState(false);
    const [removingAssignmentId, setRemovingAssignmentId] = useState<
        string | null
    >(null);
    const [fetchError, setFetchError] = useState<string | null>(null);

    const companySelectOptions = useMemo<FormSelectOption[]>(
        () => [
            { id: '', label: 'Selecciona una empresa' },
            ...companies.map((c) => ({ id: c.id, label: c.name })),
        ],
        [companies],
    );

    const roleSelectOptions = useMemo<FormSelectOption[]>(
        () => [
            { id: '', label: 'Selecciona un rol' },
            ...roleOptions.map((r) => ({ id: r.id, label: r.name })),
        ],
        [roleOptions],
    );

    const effectiveCompanyId =
        listMode === 'owner' && sessionCompanyId
            ? sessionCompanyId
            : companyId;

    const showRoleBlock = effectiveCompanyId.trim() !== '';

    const reloadUsersTable = useCallback(() => {
        router.reload({
            only: [...TABLEDATA_LIST_INERTIA_ONLY],
        });
    }, []);

    const fetchAssignments = useCallback(async (userId: string) => {
        setLoadingAssignments(true);

        try {
            const res = await fetch(userCompanyRoleAssignmentsUrl(userId), {
                credentials: 'same-origin',
                headers: jsonFetchHeaders(),
            });

            if (!res.ok) {
                throw new Error('No se pudieron cargar las asignaciones.');
            }

            const json = (await res.json()) as { data: CompanyRoleAssignmentRow[] };
            setAssignments(json.data ?? []);
        } catch {
            setAssignments([]);
        } finally {
            setLoadingAssignments(false);
        }
    }, []);

    const fetchRolesForCompany = useCallback(
        async (cid: string, assigneeId: string) => {
            setFetchError(null);
            setLoadingRoles(true);
            setRoleOptions([]);
            setRoleId('');

            try {
                const res = await fetch(
                    companyAssignableRolesUrl(cid, assigneeId),
                    {
                        credentials: 'same-origin',
                        headers: jsonFetchHeaders(),
                    },
                );

                if (!res.ok) {
                    throw new Error('No se pudieron cargar los roles.');
                }

                const json = (await res.json()) as { data: RoleOption[] };
                setRoleOptions(json.data ?? []);
            } catch {
                setFetchError('No se pudieron cargar los roles. Intenta de nuevo.');
                setRoleOptions([]);
            } finally {
                setLoadingRoles(false);
            }
        },
        [],
    );

    useEffect(() => {
        if (!open || !user) {
            return;
        }

        setFetchError(null);
        setRoleId('');
        setRoleOptions([]);

        if (listMode === 'owner' && sessionCompanyId) {
            setCompanyId(sessionCompanyId);
            void fetchRolesForCompany(sessionCompanyId, user.id);
        } else {
            setCompanyId('');
        }

        void fetchAssignments(user.id);
    }, [
        open,
        user,
        listMode,
        sessionCompanyId,
        fetchAssignments,
        fetchRolesForCompany,
    ]);

    const handleCompanyChange = useCallback(
        (nextCompanyId: string) => {
            setCompanyId(nextCompanyId);
            setRoleId('');
            setRoleOptions([]);

            if (!user || nextCompanyId === '') {
                return;
            }

            void fetchRolesForCompany(nextCompanyId, user.id);
        },
        [fetchRolesForCompany, user],
    );

    const handleAddRole = useCallback(async () => {
        if (!user || effectiveCompanyId === '' || roleId === '') {
            return;
        }

        setAdding(true);

        try {
            const res = await fetch(userCompanyRoleAssignmentsUrl(user.id), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    ...jsonFetchHeaders(),
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    company_id: effectiveCompanyId,
                    role_id: roleId,
                }),
            });

            const json = (await res.json().catch(() => ({}))) as {
                data?: CompanyRoleAssignmentRow[];
                message?: string;
            };

            if (!res.ok) {
                toast.error(
                    json.message ?? 'No se pudo agregar el rol. Revisa los datos.',
                );

                return;
            }

            setAssignments(json.data ?? []);
            setRoleId('');
            toast.success('Rol agregado.');
            reloadUsersTable();
        } catch {
            toast.error('No se pudo agregar el rol. Intenta de nuevo.');
        } finally {
            setAdding(false);
        }
    }, [user, effectiveCompanyId, roleId, reloadUsersTable]);

    const handleRemoveAssignment = useCallback(
        async (assignmentId: string) => {
            if (!user) {
                return;
            }

            setRemovingAssignmentId(assignmentId);

            try {
                const res = await fetch(
                    userCompanyRoleAssignmentUrl(user.id, assignmentId),
                    {
                        method: 'DELETE',
                        credentials: 'same-origin',
                        headers: {
                            ...jsonFetchHeaders(),
                            Accept: 'application/json',
                        },
                    },
                );

                const json = (await res.json().catch(() => ({}))) as {
                    data?: CompanyRoleAssignmentRow[];
                    message?: string;
                };

                if (!res.ok) {
                    toast.error(
                        json.message ??
                            'No se pudo quitar el rol. Intenta de nuevo.',
                    );

                    return;
                }

                setAssignments(json.data ?? []);
                toast.success('Rol quitado.');
                reloadUsersTable();
            } catch {
                toast.error('No se pudo quitar el rol. Intenta de nuevo.');
            } finally {
                setRemovingAssignmentId(null);
            }
        },
        [user, reloadUsersTable],
    );

    const canAdd =
        effectiveCompanyId.trim() !== '' &&
        roleId.trim() !== '' &&
        !loadingRoles &&
        !adding;

    return {
        companyId,
        companySelectOptions,
        roleId,
        setRoleId,
        roleOptions,
        roleSelectOptions,
        assignments,
        loadingRoles,
        loadingAssignments,
        adding,
        removingAssignmentId,
        fetchError,
        listMode,
        showRoleBlock,
        handleCompanyChange,
        handleAddRole,
        handleRemoveAssignment,
        canAdd,
    };
}
