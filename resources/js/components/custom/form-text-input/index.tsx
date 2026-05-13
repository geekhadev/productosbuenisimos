import { useId } from 'react';

import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

import type { FormTextInputProps } from './types';

export type { FormTextInputProps } from './types';

export function FormTextInput({
    type = 'text',
    placeholder = 'Ingrese un valor',
    label = undefined,
    required = false,
    error,
    containerClassName,
    inputClassName,
    labelClassName,
    errorClassName,
    inputProps = {},
}: FormTextInputProps) {
    const baseId = useId();
    const inputId = inputProps.id ?? baseId;
    const errorMessageId = `${baseId}-error`;
    const trimmedError = error?.trim() ?? '';
    const hasError = trimmedError.length > 0;

    const describedByParts = [
        inputProps['aria-describedby'],
        hasError ? errorMessageId : undefined,
    ].filter(Boolean) as string[];
    const ariaDescribedBy =
        describedByParts.length > 0 ? describedByParts.join(' ') : undefined;

    return (
        <div className={cn('grid w-full gap-2', containerClassName)}>
            {label ? (
                <Label htmlFor={inputId} className={labelClassName}>
                    {label}
                    {required ? <span aria-hidden="true"> (*)</span> : null}
                </Label>
            ) : null}
            <Input
                {...inputProps}
                id={inputId}
                type={type}
                placeholder={placeholder}
                required={required}
                className={cn(inputProps.className, inputClassName)}
                aria-invalid={hasError ? true : inputProps['aria-invalid']}
                aria-describedby={ariaDescribedBy}
            />
            <InputError
                id={errorMessageId}
                message={hasError ? trimmedError : undefined}
                className={errorClassName}
            />
        </div>
    );
}
