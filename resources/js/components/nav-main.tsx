import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import type { ReactElement } from 'react';
import { useState } from 'react';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    useSidebar,
} from '@/components/ui/sidebar';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import { isNavParent } from '@/types';
import type { NavItem, NavParentItem } from '@/types';

type ManualOpenSection = { parentTitle: string; pathname: string };

export function NavMain({ items = [] }: { items: NavItem[] }) {
    const { currentUrl, isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

    const [manualOpen, setManualOpen] = useState<ManualOpenSection | null>(
        null,
    );

    const [accordionDismissed, setAccordionDismissed] =
        useState<ManualOpenSection | null>(null);

    const manualOpenApplies =
        manualOpen !== null && manualOpen.pathname === currentUrl;

    const dismissApplies =
        accordionDismissed !== null &&
        accordionDismissed.pathname === currentUrl;

    const handleParentOpenChange = (
        item: NavParentItem,
        nextOpen: boolean,
    ): void => {
        const childActive = item.items.some((c) =>
            isCurrentOrParentUrl(c.href),
        );

        if (nextOpen) {
            setAccordionDismissed((d) =>
                d?.pathname === currentUrl && d.parentTitle === item.title
                    ? null
                    : d,
            );
            setManualOpen({
                parentTitle: item.title,
                pathname: currentUrl,
            });

            return;
        }

        setManualOpen((prev) =>
            prev?.parentTitle === item.title ? null : prev,
        );

        if (childActive) {
            setAccordionDismissed({
                parentTitle: item.title,
                pathname: currentUrl,
            });
        } else {
            setAccordionDismissed((d) =>
                d?.pathname === currentUrl && d.parentTitle === item.title
                    ? null
                    : d,
            );
        }
    };

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>Plataforma</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) =>
                    isNavParent(item) ? (
                        <NavMainCollapsibleParent
                            key={item.title}
                            item={item}
                            isCurrentOrParentUrl={isCurrentOrParentUrl}
                            manualOpenApplies={manualOpenApplies}
                            manualOpenParentTitle={manualOpen?.parentTitle ?? null}
                            sectionDismissed={
                                dismissApplies &&
                                accordionDismissed?.parentTitle === item.title
                            }
                            onOpenChange={(open) =>
                                handleParentOpenChange(item, open)
                            }
                        />
                    ) : (
                        <SidebarMenuItem key={item.title}>
                            <SidebarMenuButton
                                asChild
                                isActive={isCurrentUrl(item.href)}
                                tooltip={{ children: item.title }}
                            >
                                <Link href={item.href} prefetch>
                                    {item.icon && <item.icon />}
                                    <span>{item.title}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    ),
                )}
            </SidebarMenu>
        </SidebarGroup>
    );
}

function NavMainCollapsibleParent({
    item,
    isCurrentOrParentUrl,
    manualOpenApplies,
    manualOpenParentTitle,
    sectionDismissed,
    onOpenChange,
}: {
    item: NavParentItem;
    isCurrentOrParentUrl: ReturnType<
        typeof useCurrentUrl
    >['isCurrentOrParentUrl'];
    manualOpenApplies: boolean;
    manualOpenParentTitle: string | null;
    sectionDismissed: boolean;
    onOpenChange: (open: boolean) => void;
}): ReactElement {
    const { isMobile, state } = useSidebar();
    const childActive = item.items.some((c) =>
        isCurrentOrParentUrl(c.href),
    );
    const isOpen =
        (childActive && !sectionDismissed) ||
        (manualOpenApplies && manualOpenParentTitle === item.title);

    return (
        <SidebarMenuItem>
            <Collapsible
                className="group/collapsible w-full"
                open={isOpen}
                onOpenChange={onOpenChange}
            >
                <Tooltip>
                    <TooltipTrigger asChild>
                        <CollapsibleTrigger asChild>
                            <SidebarMenuButton
                                isActive={childActive}
                                className="w-full"
                            >
                                {item.icon && <item.icon />}
                                <span>{item.title}</span>
                                <ChevronRight
                                    className={cn(
                                        'ml-auto transition-transform duration-200',
                                        isOpen && 'rotate-90',
                                    )}
                                />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                    </TooltipTrigger>
                    <TooltipContent
                        side="right"
                        align="center"
                        hidden={state !== 'collapsed' || isMobile}
                    >
                        {item.title}
                    </TooltipContent>
                </Tooltip>
                <CollapsibleContent>
                    <SidebarMenuSub>
                        {item.items.map((child) => (
                            <SidebarMenuSubItem key={child.title}>
                                <SidebarMenuSubButton
                                    asChild
                                    isActive={isCurrentOrParentUrl(child.href)}
                                >
                                    <Link href={child.href} prefetch>
                                        {child.icon && <child.icon />}
                                        <span>{child.title}</span>
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        ))}
                    </SidebarMenuSub>
                </CollapsibleContent>
            </Collapsible>
        </SidebarMenuItem>
    );
}
