import { ChevronDown, FilterIcon } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';

import type { FilterDropdownProps } from './types';

export type { FilterDropdownProps } from './types';

const defaultContentClassName =
    'w-96 max-h-[min(85vh,36rem)] overflow-y-auto overflow-x-hidden p-4';

/**
 * Menú desplegable reutilizable para paneles de filtros (toolbar de listados).
 */
export function FilterDropdown({
    children,
    footer,
    triggerLabel = 'Filtros',
    align = 'end',
    triggerClassName,
    contentClassName,
    bodyClassName = 'grid',
    modal = false,
}: FilterDropdownProps) {
    const [open, setOpen] = useState(false);

    return (
        <DropdownMenu modal={modal} onOpenChange={setOpen}>
            <DropdownMenuTrigger asChild>
                <Button
                    type="button"
                    variant="outline"
                    className={cn('gap-2 shadow-xs', triggerClassName)}
                    disabled={open}
                >
                    <FilterIcon
                        className={cn('size-4', open && 'rotate-20')}
                        aria-hidden
                    />
                    {triggerLabel}
                    <ChevronDown className="size-4 opacity-70" aria-hidden />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
                align={align}
                className={cn(defaultContentClassName, contentClassName)}
                onCloseAutoFocus={(e) => e.preventDefault()}
            >
                <div className={bodyClassName}>{children}</div>
                {footer}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
