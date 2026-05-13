/**
 * GET JSON de roles asignables por empresa (spec: `/api/companies/{company}/roles`).
 */
export function companyAssignableRolesUrl(
    companyId: string,
    assignedForUserId?: string,
): string {
    const params = new URLSearchParams();

    if (assignedForUserId) {
        params.set('assigned_for_user', assignedForUserId);
    }

    const q = params.toString();

    return `/api/companies/${encodeURIComponent(companyId)}/roles${q ? `?${q}` : ''}`;
}

export function userCompanyRoleAssignmentsUrl(userId: string): string {
    return `/api/users/${encodeURIComponent(userId)}/company-role-assignments`;
}

export function userCompanyRoleAssignmentUrl(
    userId: string,
    assignmentId: string,
): string {
    return `${userCompanyRoleAssignmentsUrl(userId)}/${encodeURIComponent(assignmentId)}`;
}
