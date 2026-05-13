import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Trash2 } from 'lucide-react';
import type { CompanyRoleAssignmentRow } from '@/pages/configuration/users/company-roles-dialog.types';

export type CompanyRolesAssignmentsTableProps = {
    assignments: CompanyRoleAssignmentRow[];
    loadingAssignments: boolean;
    removingAssignmentId: string | null;
    onRemoveAssignment: (assignmentId: string) => void;
};

export function CompanyRolesAssignmentsTable({
    assignments,
    loadingAssignments,
    removingAssignmentId,
    onRemoveAssignment,
}: CompanyRolesAssignmentsTableProps) {
    return (
        <div className="space-y-2">
            <Label>Asignaciones actuales</Label>
            <div className="rounded-md border border-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Empresa</TableHead>
                            <TableHead>Rol</TableHead>
                            <TableHead className="w-0 text-right">
                                <span className="sr-only">Acciones</span>
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {loadingAssignments ? (
                            <TableRow>
                                <TableCell
                                    colSpan={3}
                                    className="text-muted-foreground text-center text-sm"
                                >
                                    Cargando…
                                </TableCell>
                            </TableRow>
                        ) : assignments.length === 0 ? (
                            <TableRow>
                                <TableCell
                                    colSpan={3}
                                    className="text-muted-foreground text-center text-sm"
                                >
                                    Aún no hay roles agregados.
                                </TableCell>
                            </TableRow>
                        ) : (
                            assignments.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell>{row.company_name}</TableCell>
                                    <TableCell>{row.role_name}</TableCell>
                                    <TableCell className="text-right">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="icon"
                                            className="size-8"
                                            disabled={
                                                removingAssignmentId === row.id
                                            }
                                            onClick={() =>
                                                void onRemoveAssignment(row.id)
                                            }
                                            aria-label={`Quitar rol ${row.role_name} en ${row.company_name}`}
                                        >
                                            <Trash2 className="size-3.5 text-destructive" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </div>
        </div>
    );
}
