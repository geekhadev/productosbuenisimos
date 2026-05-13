import { ChevronDown, ChevronLeft, ChevronRight, ChevronUp } from 'lucide-react';
import * as React from 'react';
import { Chevron, DayPicker, getDefaultClassNames, type DayButtonProps } from 'react-day-picker';

import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type CalendarProps = React.ComponentProps<typeof DayPicker>;

function CalendarChevron({
    className,
    disabled,
    orientation,
    ...props
}: React.ComponentProps<typeof Chevron>) {
    const classList = cn(
        'size-4 shrink-0',
        disabled ? 'text-muted-foreground/40' : 'text-muted-foreground',
        className,
    );

    if (orientation === 'left') {
        return <ChevronLeft aria-hidden className={classList} {...props} />;
    }
    if (orientation === 'right') {
        return <ChevronRight aria-hidden className={classList} {...props} />;
    }
    if (orientation === 'up') {
        return <ChevronUp aria-hidden className={classList} {...props} />;
    }
    return <ChevronDown aria-hidden className={classList} {...props} />;
}

function CalendarDayButton({ className, day, modifiers, ...props }: DayButtonProps) {
    const ref = React.useRef<HTMLButtonElement>(null);

    React.useEffect(() => {
        if (modifiers.focused) {
            ref.current?.focus();
        }
    }, [modifiers.focused]);

    const isRangeStart = Boolean(modifiers.range_start);
    const isRangeEnd = Boolean(modifiers.range_end);
    const isRangeMiddle = Boolean(modifiers.range_middle);

    return (
        <button
            ref={ref}
            type="button"
            className={cn(
                buttonVariants({ variant: 'ghost' }),
                'size-9 p-0 font-normal text-foreground transition-none hover:bg-transparent focus-visible:z-10',
                isRangeMiddle && 'rounded-none bg-transparent hover:bg-transparent',
                isRangeStart &&
                    isRangeEnd &&
                    'rounded-md bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground',
                isRangeStart &&
                    !isRangeEnd &&
                    'rounded-l-md rounded-r-none bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground',
                !isRangeStart &&
                    isRangeEnd &&
                    'rounded-l-none rounded-r-md bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground',
                modifiers.selected &&
                    !isRangeStart &&
                    !isRangeEnd &&
                    !isRangeMiddle &&
                    'rounded-md bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground',
                modifiers.today &&
                    !modifiers.selected &&
                    'text-foreground [&:not([data-range-middle])]:bg-accent/60',
                !modifiers.selected && !isRangeMiddle && 'hover:bg-accent/50',
                className,
            )}
            {...props}
        />
    );
}

function Calendar({
    className,
    classNames,
    components,
    showOutsideDays = true,
    ...props
}: CalendarProps) {
    const defaults = getDefaultClassNames();

    return (
        <DayPicker
            showOutsideDays={showOutsideDays}
            className={cn('p-3', className)}
            classNames={{
                root: cn('w-fit', defaults.root),
                months: cn('relative flex flex-col gap-4', defaults.months),
                month: cn('flex w-full flex-col gap-2', defaults.month),
                nav: cn(
                    'absolute inset-x-0 top-0 z-10 flex w-full items-center justify-between px-1',
                    defaults.nav,
                ),
                button_previous: cn(
                    buttonVariants({ variant: 'ghost' }),
                    'size-8 shrink-0 p-0 text-muted-foreground hover:bg-accent hover:text-foreground',
                    'aria-disabled:pointer-events-none aria-disabled:opacity-40',
                    defaults.button_previous,
                ),
                button_next: cn(
                    buttonVariants({ variant: 'ghost' }),
                    'size-8 shrink-0 p-0 text-muted-foreground hover:bg-accent hover:text-foreground',
                    'aria-disabled:pointer-events-none aria-disabled:opacity-40',
                    defaults.button_next,
                ),
                month_caption: cn(
                    'flex h-10 w-full items-center justify-center px-12',
                    defaults.month_caption,
                ),
                caption_label: cn(
                    'select-none text-sm font-medium text-foreground',
                    defaults.caption_label,
                ),
                month_grid: cn('w-full border-collapse', defaults.month_grid),
                weekdays: cn(defaults.weekdays),
                weekday: cn(
                    'h-9 w-9 p-0 text-center text-[0.8rem] font-normal text-muted-foreground select-none',
                    defaults.weekday,
                ),
                week: cn(defaults.week),
                day: cn(
                    'relative p-0 text-center align-middle [&:last-child[data-selected=true]_button]:rounded-r-md',
                    '[&:first-child[data-selected=true]_button]:rounded-l-md',
                    defaults.day,
                ),
                day_button: cn(
                    'size-9 min-w-9 font-normal transition-none',
                    defaults.day_button,
                ),
                range_start: cn('rounded-l-md bg-muted', defaults.range_start),
                range_middle: cn('rounded-none bg-muted', defaults.range_middle),
                range_end: cn('rounded-r-md bg-muted', defaults.range_end),
                today: cn('text-foreground', defaults.today),
                outside: cn('text-muted-foreground/60', defaults.outside),
                disabled: cn('text-muted-foreground opacity-40', defaults.disabled),
                hidden: cn('invisible', defaults.hidden),
                ...classNames,
            }}
            components={{
                Chevron: CalendarChevron,
                DayButton: CalendarDayButton,
                ...components,
            }}
            {...props}
        />
    );
}

export { Calendar, CalendarDayButton, type CalendarProps };
