import {
    Boxes,
    Building2,
    Globe,
    LayoutGrid,
    MapPin,
    Package,
    Share2,
    Bot,
    BrainCircuit,
    ClipboardList,
    Contact,
    MessageSquare,
    MessageSquareText,
    ShoppingCart,
    UserRound,
    Users,
    Warehouse,
} from 'lucide-react';
import { dashboard } from '@/routes';
import { edit as aiProvidersEdit } from '@/routes/configuration/ai-providers';
import { index as companiesIndex } from '@/routes/configuration/companies';
import { edit as fulfillmentProvidersEdit } from '@/routes/configuration/fulfillment-providers';
import { index as rolesIndex } from '@/routes/configuration/roles';
import { index as usersIndex } from '@/routes/configuration/users';
import { edit as whatsappProvidersEdit } from '@/routes/configuration/whatsapp-providers';
import { edit as agentEdit } from '@/routes/sales/agent';
import { index as conversationsIndex } from '@/routes/sales/conversations';
import { index as customersIndex } from '@/routes/sales/customers';
import { index as leadsIndex } from '@/routes/sales/leads';
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
                title: 'Leads',
                href: leadsIndex(),
                icon: Contact,
                permission: 'sales.leads.list',
            },
            {
                title: 'Pedidos',
                href: ordersIndex(),
                icon: ClipboardList,
                permission: 'sales.orders.list',
            },
            {
                title: 'Conversaciones',
                href: conversationsIndex(),
                icon: MessageSquareText,
                permission: 'sales.conversations.list',
            },
            {
                title: 'Agente de Ventas',
                href: agentEdit(),
                icon: Bot,
                permission: 'sales.agent.list',
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
            {
                title: 'Prov. IA',
                href: aiProvidersEdit(),
                icon: BrainCircuit,
                permission: 'configuration.ai-providers.list',
            },
            {
                title: 'Prov. fulfillment',
                href: fulfillmentProvidersEdit(),
                icon: Package,
                permission: 'configuration.fulfillment-providers.list',
            },
            {
                title: 'Prov. WhatsApp',
                href: whatsappProvidersEdit(),
                icon: MessageSquare,
                permission: 'configuration.whatsapp-providers.list',
            },
        ],
    },
];
