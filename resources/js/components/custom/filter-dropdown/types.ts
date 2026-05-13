import type { ReactNode } from 'react';

export type FilterDropdownProps = {
    children: ReactNode;
    /** Acciones bajo el formulario (p. ej. aplicar / reiniciar). */
    footer?: ReactNode;
    /** Texto del botón disparador. */
    triggerLabel?: string;
    align?: 'start' | 'center' | 'end';
    triggerClassName?: string;
    contentClassName?: string;
    /** Clases del contenedor del cuerpo (por defecto `grid gap-4`). */
    bodyClassName?: string;
    /** Radix: `false` evita trap de foco en portales (útil con inputs). */
    modal?: boolean;
};
