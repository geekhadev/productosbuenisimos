import { X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { CompanyRolesAddForm } from '@/pages/configuration/users/company-roles-add-form';
import { CompanyRolesAssignmentsTable } from '@/pages/configuration/users/company-roles-assignments-table';
import type { CompanyRolesDialogProps } from '@/pages/configuration/users/company-roles-dialog.types';
import { useCompanyRolesDialog } from '@/pages/configuration/users/hooks/use-company-roles-dialog';

export type {
    CompanyRoleAssignmentRow,
    CompanyRolesDialogProps,
    RoleOption,
} from '@/pages/configuration/users/company-roles-dialog.types';

export function CompanyRolesDialog({
    open,
    onOpenChange,
    user,
    listMode,
    companies,
    sessionCompanyId,
    sessionCompanyName,
}: CompanyRolesDialogProps) {
    const {
        companyId,
        companySelectOptions,
        roleId,
        setRoleId,
        roleOptions,
        roleSelectOptions,
        assignments,
        loadingRoles,
        loadingAssignments,
        removingAssignmentId,
        fetchError,
        showRoleBlock,
        handleCompanyChange,
        handleAddRole,
        handleRemoveAssignment,
        canAdd,
    } = useCompanyRolesDialog({
        open,
        user,
        listMode,
        companies,
        sessionCompanyId,
    });

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>Empresa y roles</DialogTitle>
                    <DialogDescription>
                        Elige empresa y rol, luego usa «Agregar rol» para registrar cada
                        combinación. Los cambios se guardan al instante; puedes seguir
                        añadiendo sin cerrar el modal.
                    </DialogDescription>
                </DialogHeader>

                {user ? (
                    <div className="space-y-6">
                        <div className="text-muted-foreground text-sm">
                            <span className="font-medium text-foreground">
                                {user.name}
                            </span>
                            <span className="mx-1">·</span>
                            <span>{user.email}</span>
                        </div>

                        <CompanyRolesAddForm
                            listMode={listMode}
                            sessionCompanyName={sessionCompanyName}
                            companyId={companyId}
                            companySelectOptions={companySelectOptions}
                            onCompanyChange={handleCompanyChange}
                            showRoleBlock={showRoleBlock}
                            loadingRoles={loadingRoles}
                            roleOptions={roleOptions}
                            roleSelectOptions={roleSelectOptions}
                            roleId={roleId}
                            onRoleChange={setRoleId}
                            canAdd={canAdd}
                            onAddRole={handleAddRole}
                            fetchError={fetchError}
                        />

                        <CompanyRolesAssignmentsTable
                            assignments={assignments}
                            loadingAssignments={loadingAssignments}
                            removingAssignmentId={removingAssignmentId}
                            onRemoveAssignment={handleRemoveAssignment}
                        />

                        <DialogFooter className="gap-2 sm:justify-end">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => onOpenChange(false)}
                            >
                                <X />
                                Cerrar
                            </Button>
                        </DialogFooter>
                    </div>
                ) : null}
            </DialogContent>
        </Dialog>
    );
}
