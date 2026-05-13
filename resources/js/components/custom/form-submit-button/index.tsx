import { useId } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';

import type { FormSubmitButtonProps } from './types';

export type { FormSubmitButtonProps } from './types';

export function FormSubmitButton({
    loading = false,
    icon = null,
    label = 'Botón',
    labelLoading = 'Cargando...',
    error,
    containerClassName,
    buttonClassName,
    errorClassName,
    className,
    disabled,
    ...buttonProps
}: FormSubmitButtonProps) {
    const baseId = useId();
    const errorMessageId = `${baseId}-error`;
    const trimmedError = error?.trim() ?? '';
    const hasError = trimmedError.length > 0;

    const { 'aria-describedby': ariaDescribedByProp, ...restButtonProps } =
        buttonProps;

    const describedByParts = [
        ariaDescribedByProp,
        hasError ? errorMessageId : undefined,
    ].filter(Boolean) as string[];
    const ariaDescribedBy =
        describedByParts.length > 0 ? describedByParts.join(' ') : undefined;

    return (
        <div className={cn('grid gap-2', containerClassName)}>
            <Button
                {...restButtonProps}
                className={cn(className, buttonClassName)}
                disabled={disabled || loading}
                aria-busy={loading || undefined}
                aria-describedby={ariaDescribedBy}
            >
                {loading ? (
                    <>
                        <Spinner />
                        {labelLoading}
                    </>
                ) : (
                    <>
                        {icon}
                        {label}
                    </>
                )}
            </Button>
            <InputError
                id={errorMessageId}
                message={hasError ? trimmedError : undefined}
                className={errorClassName}
            />
        </div>
    );
}
