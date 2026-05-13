import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

import type { FormActionButtonProps } from './types';

export type { FormActionButtonProps } from './types';

export function FormActionButton({
    icon = null,
    label,
    containerClassName,
    buttonClassName,
    className,
    ...buttonProps
}: FormActionButtonProps) {
    return (
        <div className={cn('grid w-full gap-2', containerClassName)}>
            <Button
                {...buttonProps}
                className={cn(
                    'inline-flex items-center gap-2 hover:cursor-pointer',
                    className,
                    buttonClassName,
                )}
            >
                {icon}
                {label}
            </Button>
        </div>
    );
}
