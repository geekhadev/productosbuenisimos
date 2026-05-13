import { Eye, EyeOff } from 'lucide-react';
import { useId, useState } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

import type { FormPasswordInputGroupProps } from './types';

export type { FormPasswordInputGroupProps } from './types';

export function FormPasswordInputGroup({
    label = undefined,
    required = false,
    error,
    containerClassName,
    inputClassName,
    labelClassName,
    errorClassName,
    placeholder = '********',
    inputProps = {},
}: FormPasswordInputGroupProps) {
    const baseId = useId();
    const inputId = inputProps.id ?? baseId;
    const errorMessageId = `${baseId}-error`;
    const trimmedError = error?.trim() ?? '';
    const hasError = trimmedError.length > 0;
    const [visible, setVisible] = useState(false);

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
            <div className="relative">
                <Input
                    {...inputProps}
                    id={inputId}
                    type={visible ? 'text' : 'password'}
                    required={required}
                    placeholder={placeholder}
                    className={cn('pe-10', inputProps.className, inputClassName)}
                    aria-invalid={hasError ? true : inputProps['aria-invalid']}
                    aria-describedby={ariaDescribedBy}
                />
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="text-muted-foreground hover:text-foreground absolute end-1 top-1/2 size-8 -translate-y-1/2 rounded-md"
                    aria-pressed={visible}
                    aria-label={visible ? 'Ocultar contraseña' : 'Mostrar contraseña'}
                    onClick={() => {
                        setVisible((v) => !v);
                    }}
                >
                    {visible ? <EyeOff className="size-4" /> : <Eye className="size-4" />}
                </Button>
            </div>
            <InputError
                id={errorMessageId}
                message={hasError ? trimmedError : undefined}
                className={errorClassName}
            />
        </div>
    );
}
