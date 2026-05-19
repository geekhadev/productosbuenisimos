import {
    Boxes,
    Building2,
    Globe,
    KeyRound,
    LayoutGrid,
    MapPin,
    Package,
    Share2,
    ClipboardList,
    ShoppingCart,
    UserRound,
    Users,
    Warehouse,
} from 'lucide-react';
import { dashboard } from '@/routes';
import { index as modulesIndex } from '@/routes/administration/modules';
import { index as permissionsIndex } from '@/routes/administration/permissions';
import { index as systemsIndex } from '@/routes/administration/systems';
import { index as companiesIndex } from '@/routes/configuration/companies';
import { index as rolesIndex } from '@/routes/configuration/roles';
import { index as usersIndex } from '@/routes/configuration/users';
import { index as customersIndex } from '@/routes/sales/customers';
import { index as ordersIndex } from '@/routes/sales/orders';
import { index as countriesIndex } from '@/routes/shared/countries';
import { index as statesIndex } from '@/routes/shared/states';
import { index as productsIndex } from '@/routes/stock/products';
import type { NavItem } from '@/types';

export const mainNavItems: NavItem[] = [
    {
        title: 'Panel',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Administración',
        icon: Boxes,
        items: [
            {
                title: 'Sistemas',
                href: systemsIndex(),
                icon: Boxes,
            },
            {
                title: 'Módulos',
                href: modulesIndex(),
                icon: Package,
            },
            {
                title: 'Permisos',
                href: permissionsIndex(),
                icon: KeyRound,
            },
        ],
    },
    {
        title: 'Stock',
        icon: Warehouse,
        items: [
            {
                title: 'Productos',
                href: productsIndex(),
                icon: Package,
                permission: 'stock.products.list',
            },
        ],
    },
    {
        title: 'Ventas',
        icon: ShoppingCart,
        items: [
            {
                title: 'Clientes',
                href: customersIndex(),
                icon: Users,
                permission: 'sales.customers.list',
            },
            {
                title: 'Pedidos',
                href: ordersIndex(),
                icon: ClipboardList,
                permission: 'sales.orders.list',
            },
        ],
    },
    {
        title: 'Compartido',
        icon: Share2,
        items: [
            {
                title: 'Países',
                href: countriesIndex(),
                icon: Globe,
                permission: 'shared.countries.list',
            },
            {
                title: 'Estados',
                href: statesIndex(),
                icon: MapPin,
                permission: 'shared.states.list',
            },
        ],
    },
    {
        title: 'Configuración',
        icon: Boxes,
        items: [
            {
                title: 'Empresas',
                href: companiesIndex(),
                icon: Building2,
                permission: 'configuration.companies.list',
            },
            {
                title: 'Roles',
                href: rolesIndex(),
                icon: UserRound,
                permission: 'configuration.roles.list',
            },
            {
                title: 'Usuarios',
                href: usersIndex(),
                icon: Users,
            },
        ],
    },
];
