import type { InertiaLinkProps } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

/** Enlace simple del menú (siempre con `href`). */
export type NavLeafItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    permission?: string;
};

/** Grupo con subenlaces; el padre no navega por sí mismo. */
export type NavParentItem = {
    title: string;
    icon?: LucideIcon | null;
    items: NavLeafItem[];
};

export type NavItem = NavLeafItem | NavParentItem;

export function isNavParent(item: NavItem): item is NavParentItem {
    return 'items' in item && Array.isArray(item.items);
}

export function flattenNavItemsToLeaves(items: NavItem[]): NavLeafItem[] {
    return items.flatMap((item) => (isNavParent(item) ? item.items : [item]));
}
