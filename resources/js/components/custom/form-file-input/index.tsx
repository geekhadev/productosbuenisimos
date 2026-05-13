import { useId } from 'react';

import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

import type { FormFileInputProps } from './types';

export type { FormFileInputProps } from './types';

export function FormFileInput({
    label = undefined,
    required = false,
    error,
    labelAccessory = undefined,
    containerClassName,
    inputClassName,
    labelClassName,
    errorClassName,
    inputProps = {},
}: FormFileInputProps) {
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
            {label && labelAccessory ? (
                <div className="flex flex-wrap items-baseline justify-between gap-x-2 gap-y-1">
                    <Label htmlFor={inputId} className={labelClassName}>
                        {label}
                        {required ? <span aria-hidden="true"> (*)</span> : null}
                    </Label>
                    {labelAccessory}
                </div>
            ) : null}
            {label && !labelAccessory ? (
                <Label htmlFor={inputId} className={labelClassName}>
                    {label}
                    {required ? <span aria-hidden="true"> (*)</span> : null}
                </Label>
            ) : null}
            {!label && labelAccessory ? (
                <div className="flex flex-wrap justify-end gap-x-2 gap-y-1">{labelAccessory}</div>
            ) : null}
            <Input
                {...inputProps}
                id={inputId}
                type="file"
                required={required}
                className={cn('cursor-pointer', inputProps.className, inputClassName)}
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
