import type { NavItem } from '@/types';
import { isNavParent } from '@/types';
import type { User } from '@/types/auth';

const ROOT_ONLY_SCOPES = new Set(['Administración', 'Compartido']);

export function filterNavByUser(items: NavItem[], user: User, permissions: string[]): NavItem[] {
    if (user.type === 'root') {
        return items;
    }

    const userPermissions = new Set(permissions);

    return items.reduce<NavItem[]>((acc, item) => {
        if (isNavParent(item)) {
            if (ROOT_ONLY_SCOPES.has(item.title)) {
                return acc;
            }

            const visibleChildren = item.items.filter((child) => {
                return child.permission === undefined || userPermissions.has(child.permission);
            });

            if (visibleChildren.length === 0) {
                return acc;
            }

            acc.push({
                ...item,
                items: visibleChildren,
            });

            return acc;
        }

        if (item.permission !== undefined && !userPermissions.has(item.permission)) {
            return acc;
        }

        acc.push(item);

        return acc;
    }, []);
}
