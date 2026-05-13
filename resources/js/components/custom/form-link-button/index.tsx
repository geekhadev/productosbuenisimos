import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

import type { FormLinkButtonProps } from './types';

export type { FormLinkButtonProps } from './types';

export function FormLinkButton({
    icon = null,
    label,
    buttonVariant = 'default',
    buttonSize,
    containerClassName,
    buttonClassName,
    className,
    ...linkProps
}: FormLinkButtonProps) {
    return (
        <div className={cn('grid w-full gap-2', containerClassName)}>
            <Button
                variant={buttonVariant}
                size={buttonSize}
                className={cn(
                    'inline-flex items-center gap-2',
                    className,
                    buttonClassName,
                )}
                asChild
            >
                <Link {...linkProps}>
                    {icon}
                    {label}
                </Link>
            </Button>
        </div>
    );
}
