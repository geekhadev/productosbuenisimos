import { cn } from '@/lib/utils';
import type { DateDisplayMode, DateDisplayProps } from './types';

export type { DateDisplayMode, DateDisplayProps } from './types';

function pad2(n: number): string {
    return String(n).padStart(2, '0');
}

function parseToDate(
    value: string | Date | null | undefined,
): Date | null {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    if (value instanceof Date) {
        return Number.isNaN(value.getTime()) ? null : value;
    }

    const d = new Date(value);

    return Number.isNaN(d.getTime()) ? null : d;
}

/**
 * Formato fijo `dd/mm/aaaa` y hora `HH:mm:ss` (24 h) en hora local.
 */
export function formatDateDisplay(
    value: string | Date | null | undefined,
    mode: DateDisplayMode,
    empty = '—',
): string {
    const d = parseToDate(value);

    if (!d) {
        return empty;
    }

    const datePart = `${pad2(d.getDate())}/${pad2(d.getMonth() + 1)}/${d.getFullYear()}`;
    const timePart = `${pad2(d.getHours())}:${pad2(d.getMinutes())}:${pad2(d.getSeconds())}`;

    if (mode === 'date') {
        return datePart;
    }

    if (mode === 'time') {
        return timePart;
    }

    return `${datePart} ${timePart}`;
}

export function DateDisplay({
    value,
    mode,
    empty = '—',
    as = 'time',
    className,
}: DateDisplayProps) {
    const d = parseToDate(value);
    const text = d ? formatDateDisplay(d, mode, empty) : empty;

    if (!d || as === 'span') {
        return <span className={cn(className)}>{text}</span>;
    }

    return (
        <time dateTime={d.toISOString()} className={cn(className)}>
            {text}
        </time>
    );
}
