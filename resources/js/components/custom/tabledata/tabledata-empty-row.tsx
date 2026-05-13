import type { ReactNode } from 'react';
import { TABLEDATA_EMPTY_MESSAGE_DEFAULT } from '@/components/custom/tabledata/config';
import type { TabledataDensity } from '@/components/custom/tabledata/tabledata.types';
import { TableCell, TableRow } from '@/components/ui/table';
import { cn } from '@/lib/utils';

type TabledataEmptyRowProps = {
    colSpan: number;
    message?: ReactNode;
    className?: string;
    density?: TabledataDensity;
};

/**
 * Fila única para tablas de datos cuando no hay registros (p. ej. listados paginados).
 */
export function TabledataEmptyRow({
    colSpan,
    message = TABLEDATA_EMPTY_MESSAGE_DEFAULT,
    className,
    density = 'normal',
}: TabledataEmptyRowProps) {
    const densityCellClass =
        density === 'compact' ? 'px-1.5 py-2 text-sm' : undefined;

    return (
        <TableRow>
            <TableCell
                colSpan={colSpan}
                className={cn(
                    'text-center text-muted-foreground',
                    densityCellClass,
                    className,
                )}
            >
                {message}
            </TableCell>
        </TableRow>
    );
}
