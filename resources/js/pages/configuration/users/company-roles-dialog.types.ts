import type { CompanyOption, UserListRow } from '@/pages/configuration/users/types';

export type RoleOption = {
    id: string;
    name: string;
};

export type CompanyRoleAssignmentRow = {
    id: string;
    company_id: string;
    company_name: string;
    role_id: string;
    role_name: string;
};

export type CompanyRolesDialogProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    user: UserListRow | null;
    listMode: 'root' | 'owner';
    companies: CompanyOption[];
    sessionCompanyId: string | null;
    sessionCompanyName: string | null;
};
