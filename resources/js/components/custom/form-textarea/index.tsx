import { useId } from 'react';

import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';

import type { FormTextareaProps } from './types';

export type { FormTextareaProps } from './types';

export function FormTextarea({
    placeholder = 'Ingrese un valor',
    label = 'Ingrese un valor',
    required = false,
    error,
    containerClassName,
    textareaClassName,
    labelClassName,
    errorClassName,
    textareaProps = {},
}: FormTextareaProps) {
    const baseId = useId();
    const textareaId = textareaProps.id ?? baseId;
    const errorMessageId = `${baseId}-error`;
    const trimmedError = error?.trim() ?? '';
    const hasError = trimmedError.length > 0;

    const describedByParts = [
        textareaProps['aria-describedby'],
        hasError ? errorMessageId : undefined,
    ].filter(Boolean) as string[];
    const ariaDescribedBy =
        describedByParts.length > 0 ? describedByParts.join(' ') : undefined;

    return (
        <div className={cn('grid w-full gap-2', containerClassName)}>
            <Label htmlFor={textareaId} className={labelClassName}>
                {label}
                {required ? <span aria-hidden="true"> (*)</span> : null}
            </Label>
            <Textarea
                {...textareaProps}
                id={textareaId}
                placeholder={placeholder}
                required={required}
                className={cn(textareaProps.className, textareaClassName)}
                aria-invalid={hasError ? true : textareaProps['aria-invalid']}
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
