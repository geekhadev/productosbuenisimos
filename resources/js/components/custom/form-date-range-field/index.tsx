import { format, isValid, parse } from 'date-fns';
import { es } from 'date-fns/locale';
import { CalendarIcon } from 'lucide-react';
import { useId, useMemo } from 'react';
import type { DateRange } from 'react-day-picker';

import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';

import type { FormDateRangeFieldProps } from './types';

export type { FormDateRangeFieldProps } from './types';

function parseYmd(value: string): Date | undefined {
    const trimmed = value.trim();

    if (trimmed === '') {
        return undefined;
    }

    const parsed = parse(trimmed, 'yyyy-MM-dd', new Date());

    return isValid(parsed) ? parsed : undefined;
}

export function FormDateRangeField({
    fromValue,
    toValue,
    onRangeChange,
    label,
    id: idProp,
    placeholder = 'Seleccionar rango de fechas',
    containerClassName,
    labelClassName,
    popoverContentClassName,
}: FormDateRangeFieldProps) {
    const generatedId = useId();
    const fieldId = idProp ?? generatedId;

    const selected = useMemo((): DateRange | undefined => {
        const from = parseYmd(fromValue);
        const to = parseYmd(toValue);

        if (!from && !to) {
            return undefined;
        }

        return { from: from ?? to, to: to ?? from };
    }, [fromValue, toValue]);

    return (
        <div className={cn('grid w-full gap-2', containerClassName)}>
            {label ? (
                <Label htmlFor={fieldId} className={labelClassName}>
                    {label}
                </Label>
            ) : null}
            <Popover>
                <PopoverTrigger asChild>
                    <Button
                        id={fieldId}
                        type="button"
                        variant="outline"
                        className={cn(
                            'h-9 w-full justify-start px-3 font-normal',
                            !selected?.from && 'text-muted-foreground',
                        )}
                    >
                        <CalendarIcon aria-hidden className="size-4 shrink-0" />
                        {selected?.from ? (
                            selected.to ? (
                                <>
                                    {format(selected.from, 'd MMM y', {
                                        locale: es,
                                    })}{' '}
                                    —{' '}
                                    {format(selected.to, 'd MMM y', {
                                        locale: es,
                                    })}
                                </>
                            ) : (
                                format(selected.from, 'd MMM y', { locale: es })
                            )
                        ) : (
                            <span>{placeholder}</span>
                        )}
                    </Button>
                </PopoverTrigger>
                <PopoverContent
                    align="start"
                    className={cn(
                        'z-[60] w-auto border-border p-0 shadow-md',
                        popoverContentClassName,
                    )}
                >
                    <Calendar
                        locale={es}
                        defaultMonth={selected?.from}
                        mode="range"
                        onSelect={(range) => {
                            if (!range?.from) {
                                onRangeChange('', '');

                                return;
                            }

                            onRangeChange(
                                format(range.from, 'yyyy-MM-dd'),
                                range.to ? format(range.to, 'yyyy-MM-dd') : '',
                            );
                        }}
                        selected={selected}
                    />
                </PopoverContent>
            </Popover>
        </div>
    );
}
