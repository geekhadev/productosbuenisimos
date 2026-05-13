import { ChevronDownIcon } from 'lucide-react';
import { useId } from 'react';

import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

import type { FormSelectProps } from './types';

export type { FormSelectOption, FormSelectProps } from './types';

const selectBaseClassName = cn(
    'flex h-9 w-full min-w-0 appearance-none rounded-md border border-input bg-transparent px-3 py-1 pr-9 text-base text-foreground shadow-xs transition-[color,box-shadow] outline-none md:text-sm',
    'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50',
    'aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40',
    'disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50',
);

export function FormSelect({
    options,
    placeholder = 'Seleccione una opción',
    label = undefined,
    required = false,
    error,
    containerClassName,
    selectClassName,
    labelClassName,
    errorClassName,
    selectProps = {},
}: FormSelectProps) {
    const {
        className: selectPropsClassName,
        id: selectPropsId,
        defaultValue: selectDefaultValue,
        value: selectValue,
        'aria-describedby': selectAriaDescribedBy,
        'aria-invalid': selectAriaInvalid,
        ...restSelectProps
    } = selectProps;

    const baseId = useId();
    const selectId = selectPropsId ?? baseId;
    const errorMessageId = `${baseId}-error`;
    const trimmedError = error?.trim() ?? '';
    const hasError = trimmedError.length > 0;

    const describedByParts = [
        selectAriaDescribedBy,
        hasError ? errorMessageId : undefined,
    ].filter(Boolean) as string[];
    const ariaDescribedBy =
        describedByParts.length > 0 ? describedByParts.join(' ') : undefined;

    const showPlaceholderOption = placeholder.trim().length > 0;
    const isControlled = selectValue !== undefined;
    const mergedDefaultValue = isControlled
        ? undefined
        : showPlaceholderOption
          ? (selectDefaultValue ?? '')
          : selectDefaultValue;

    return (
        <div className={cn('grid w-full gap-2', containerClassName)}>
            {label ? (
                <Label htmlFor={selectId} className={labelClassName}>
                    {label}
                    {required ? <span aria-hidden="true"> (*)</span> : null}
                </Label>
            ) : null}
            <div className="relative w-full">
                <select
                    {...restSelectProps}
                    id={selectId}
                    {...(isControlled ? { value: selectValue } : {})}
                    {...(!isControlled && mergedDefaultValue !== undefined
                        ? { defaultValue: mergedDefaultValue }
                        : {})}
                    required={required}
                    className={cn(
                        selectBaseClassName,
                        selectPropsClassName,
                        selectClassName,
                    )}
                    aria-invalid={hasError ? true : selectAriaInvalid}
                    aria-describedby={ariaDescribedBy}
                >
                    {showPlaceholderOption ? (
                        <option value="" disabled>
                            {placeholder}
                        </option>
                    ) : null}
                    {options.map((opt, index) => {
                        const value = String(opt.id);

                        return (
                            <option key={`${value}-${index}`} value={value}>
                                {opt.label}
                            </option>
                        );
                    })}
                </select>
                <ChevronDownIcon
                    className="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-muted-foreground opacity-50"
                    aria-hidden
                />
            </div>
            <InputError
                id={errorMessageId}
                message={hasError ? trimmedError : undefined}
                className={errorClassName}
            />
        </div>
    );
}
