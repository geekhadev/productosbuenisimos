import { CirclePlus } from 'lucide-react';
import type { FormSelectOption } from '@/components/custom/form-select';
import { FormSelect } from '@/components/custom/form-select';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import type { RoleOption } from '@/pages/configuration/users/company-roles-dialog.types';

export type CompanyRolesAddFormProps = {
    listMode: 'root' | 'owner';
    sessionCompanyName: string | null;
    companyId: string;
    companySelectOptions: FormSelectOption[];
    onCompanyChange: (companyId: string) => void;
    showRoleBlock: boolean;
    loadingRoles: boolean;
    roleOptions: RoleOption[];
    roleSelectOptions: FormSelectOption[];
    roleId: string;
    onRoleChange: (roleId: string) => void;
    canAdd: boolean;
    onAddRole: () => void;
    fetchError: string | null;
};

export function CompanyRolesAddForm({
    listMode,
    sessionCompanyName,
    companyId,
    companySelectOptions,
    onCompanyChange,
    showRoleBlock,
    loadingRoles,
    roleOptions,
    roleSelectOptions,
    roleId,
    onRoleChange,
    canAdd,
    onAddRole,
    fetchError,
}: CompanyRolesAddFormProps) {
    return (
        <>
            <div className="flex flex-col gap-3">
                <div className="grid grid-cols-1 gap-2 md:grid-cols-2 items-center">
                    {listMode === 'root' ? (
                        <div className="min-w-0 flex-1">
                            <FormSelect
                                label="Empresa"
                                placeholder="Selecciona una empresa"
                                required
                                options={companySelectOptions}
                                selectProps={{
                                    id: 'company-roles-company_id',
                                    name: 'company_id',
                                    value: companyId,
                                    onChange: (e) =>
                                        onCompanyChange(e.target.value),
                                }}
                            />
                        </div>
                    ) : (
                        <div className="min-w-0 flex-1 space-y-2">
                            <Label>Empresa</Label>
                            <p className="text-sm">
                                {sessionCompanyName ?? 'Empresa activa'}
                            </p>
                        </div>
                    )}

                    {showRoleBlock ? (
                        <div className="min-w-0 flex-1">
                            {loadingRoles ? (
                                <p className="text-muted-foreground pb-2 text-sm">
                                    Cargando roles…
                                </p>
                            ) : roleOptions.length > 0 ? (
                                <FormSelect
                                    label="Rol"
                                    placeholder="Selecciona un rol"
                                    required
                                    options={roleSelectOptions}
                                    selectProps={{
                                        id: 'company-roles-role_id',
                                        name: 'role_id',
                                        value: roleId,
                                        onChange: (e) =>
                                            onRoleChange(e.target.value),
                                    }}
                                />
                            ) : (
                                <p className="text-muted-foreground text-sm text-center">
                                    No hay roles disponibles para esta empresa.
                                </p>
                            )}
                        </div>
                    ) : null}
                </div>

                <div className="flex shrink-0 pb-0.5 sm:pb-0 justify-end">
                    <Button
                        type="button"
                        onClick={() => void onAddRole()}
                        disabled={!canAdd}
                    >
                        <CirclePlus />
                        Agregar rol
                    </Button>
                </div>
            </div>

            {fetchError ? (
                <p className="text-destructive text-sm" role="alert">
                    {fetchError}
                </p>
            ) : null}
        </>
    );
}
