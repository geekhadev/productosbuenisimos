export type PermissionTreePermission = {
    id: string;
    name: string;
    slug: string;
};

export type PermissionTreeModule = {
    id: string;
    name: string;
    slug: string;
    permissions: PermissionTreePermission[];
};

export type PermissionTreeSystem = {
    id: string;
    name: string;
    slug: string;
    modules: PermissionTreeModule[];
};

export type MatrixRole = {
    id: string;
    name: string;
    is_public?: boolean;
    permission_ids: string[];
};

export type RolesMatrixPageProps = {
    permissionsTree: PermissionTreeSystem[];
    roles: MatrixRole[];
    companyMissing: boolean;
    canEditPublicRoles?: boolean;
};

export type MatrixFlatRow =
    | { kind: 'system'; key: string; label: string; systemId: string }
    | {
          kind: 'module';
          key: string;
          label: string;
          systemKey: string;
          systemId: string;
          moduleId: string;
      }
    | {
          kind: 'permission';
          key: string;
          id: string;
          label: string;
          moduleKey: string;
          systemKey: string;
          systemId: string;
          moduleId: string;
      };

export function permissionIdsForModule(
    tree: PermissionTreeSystem[],
    moduleId: string,
): string[] {
    for (const system of tree) {
        const mod = system.modules.find((m) => m.id === moduleId);

        if (mod) {
            return mod.permissions.map((p) => p.id);
        }
    }

    return [];
}

export function permissionIdsForSystem(
    tree: PermissionTreeSystem[],
    systemId: string,
): string[] {
    const system = tree.find((s) => s.id === systemId);

    if (!system) {
        return [];
    }

    return system.modules.flatMap((m) => m.permissions.map((p) => p.id));
}

export function flattenPermissionsTree(
    tree: PermissionTreeSystem[],
): MatrixFlatRow[] {
    const rows: MatrixFlatRow[] = [];

    for (const system of tree) {
        const systemKey = `s-${system.id}`;

        rows.push({
            kind: 'system',
            key: systemKey,
            label: system.name,
            systemId: system.id,
        });

        for (const mod of system.modules) {
            const moduleKey = `m-${mod.id}`;

            rows.push({
                kind: 'module',
                key: moduleKey,
                label: mod.name,
                systemKey,
                systemId: system.id,
                moduleId: mod.id,
            });

            for (const perm of mod.permissions) {
                rows.push({
                    kind: 'permission',
                    key: `p-${perm.id}`,
                    id: perm.id,
                    label: perm.name,
                    moduleKey,
                    systemKey,
                    systemId: system.id,
                    moduleId: mod.id,
                });
            }
        }
    }

    return rows;
}
