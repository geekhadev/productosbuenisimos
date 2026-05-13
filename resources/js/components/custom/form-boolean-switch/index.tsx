import { useId, useState } from 'react';

import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { cn } from '@/lib/utils';

export type FormBooleanSwitchProps = {
    label: string;
    name: string;
    id?: string;
    defaultChecked?: boolean;
    error?: string;
    containerClassName?: string;
    labelClassName?: string;
    errorClassName?: string;
    switchClassName?: string;
    description?: string;
};

/**
 * Campo booleano para formularios HTML: envía `0` o `1` (compatible con validación Laravel `boolean`).
 */
export function FormBooleanSwitch({
    label,
    name,
    id,
    defaultChecked = false,
    error,
    containerClassName,
    labelClassName,
    errorClassName,
    switchClassName,
    description,
}: FormBooleanSwitchProps) {
    const baseId = useId();
    const controlId = id ?? `${baseId}-${name}`;
    const errorMessageId = `${baseId}-error`;
    const trimmedError = error?.trim() ?? '';
    const hasError = trimmedError.length > 0;
    const [checked, setChecked] = useState(defaultChecked);

    return (
        <div className={cn('grid w-full gap-2', containerClassName)}>
            <div className="flex flex-row flex-wrap items-center justify-between gap-3">
                <div className="grid min-w-0 gap-1">
                    <Label htmlFor={controlId} className={labelClassName}>
                        {label}
                    </Label>
                    {description ? (
                        <p className="text-muted-foreground text-sm">{description}</p>
                    ) : null}
                </div>
                <div className="flex shrink-0 items-center gap-2">
                    <input type="hidden" name={name} value={checked ? '1' : '0'} />
                    <Switch
                        id={controlId}
                        checked={checked}
                        onCheckedChange={setChecked}
                        className={switchClassName}
                        aria-invalid={hasError ? true : undefined}
                        aria-describedby={
                            hasError ? errorMessageId : undefined
                        }
                    />
                </div>
            </div>
            <InputError
                id={errorMessageId}
                message={hasError ? trimmedError : undefined}
                className={errorClassName}
            />
        </div>
    );
}
