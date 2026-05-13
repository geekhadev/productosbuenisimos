import { format, isAfter, isValid, parse, startOfDay } from 'date-fns';
import { es } from 'date-fns/locale';
import { CalendarIcon } from 'lucide-react';
import { useId, useMemo } from 'react';
import type { Matcher } from 'react-day-picker';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';

import type { FormDatePickerFieldProps } from './types';

export type { FormDatePickerFieldProps } from './types';

function parseYmd(value: string): Date | undefined {
    const trimmed = value.trim();

    if (trimmed === '') {
        return undefined;
    }

    const parsed = parse(trimmed, 'yyyy-MM-dd', new Date());

    return isValid(parsed) ? parsed : undefined;
}

function collectMatchers(
    disableFutureDates: boolean,
    disabledProp: Matcher | Matcher[] | undefined,
): Matcher | Matcher[] | undefined {
    const matchers: Matcher[] = [];

    if (disableFutureDates) {
        matchers.push((date: Date) =>
            isAfter(startOfDay(date), startOfDay(new Date())),
        );
    }

    if (disabledProp !== undefined) {
        matchers.push(...(Array.isArray(disabledProp) ? disabledProp : [disabledProp]));
    }

    if (matchers.length === 0) {
        return undefined;
    }

    return matchers.length === 1 ? matchers[0] : matchers;
}

export function FormDatePickerField({
    value,
    onChange,
    label = undefined,
    required = false,
    error,
    placeholder = 'Seleccionar fecha',
    id: idProp,
    disableFutureDates = false,
    disabled: disabledProp,
    containerClassName,
    labelClassName,
    errorClassName,
    triggerClassName,
    popoverContentClassName,
}: FormDatePickerFieldProps) {
    const baseId = useId();
    const fieldId = idProp ?? baseId;
    const errorMessageId = `${baseId}-error`;
    const trimmedError = error?.trim() ?? '';
    const hasError = trimmedError.length > 0;

    const selected = useMemo(() => parseYmd(value), [value]);

    const disabledMatchers = useMemo(
        () => collectMatchers(disableFutureDates, disabledProp),
        [disableFutureDates, disabledProp],
    );

    const describedByParts = [hasError ? errorMessageId : undefined].filter(Boolean) as string[];
    const ariaDescribedBy =
        describedByParts.length > 0 ? describedByParts.join(' ') : undefined;

    return (
        <div className={cn('grid w-full gap-2', containerClassName)}>
            {label ? (
                <Label htmlFor={fieldId} className={labelClassName}>
                    {label}
                    {required ? <span aria-hidden="true"> (*)</span> : null}
                </Label>
            ) : null}
            <Popover>
                <PopoverTrigger asChild>
                    <Button
                        id={fieldId}
                        type="button"
                        variant="outline"
                        data-empty={!selected}
                        className={cn(
                            'h-9 w-full justify-start px-3 font-normal data-[empty=true]:text-muted-foreground',
                            triggerClassName,
                        )}
                        aria-required={required ? true : undefined}
                        aria-invalid={hasError ? true : undefined}
                        aria-describedby={ariaDescribedBy}
                    >
                        <CalendarIcon aria-hidden className="size-4 shrink-0" />
                        {selected ? (
                            format(selected, 'd MMM y', { locale: es })
                        ) : (
                            <span>{placeholder}</span>
                        )}
                    </Button>
                </PopoverTrigger>
                <PopoverContent
                    align="start"
                    className={cn(
                        'z-60 w-auto border-border p-0 shadow-md',
                        popoverContentClassName,
                    )}
                >
                    <Calendar
                        locale={es}
                        mode="single"
                        defaultMonth={selected}
                        selected={selected}
                        onSelect={(date) => {
                            if (!date) {
                                onChange('');

                                return;
                            }

                            onChange(format(date, 'yyyy-MM-dd'));
                        }}
                        disabled={disabledMatchers}
                    />
                </PopoverContent>
            </Popover>
            <InputError
                id={errorMessageId}
                message={hasError ? trimmedError : undefined}
                className={errorClassName}
            />
        </div>
    );
}
