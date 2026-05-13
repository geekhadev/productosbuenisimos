import type { ComponentProps, ReactNode } from 'react';

export type FormFileInputProps = {
    label?: string;
    /** Muestra " (*)" en el label; debe coincidir con `required` en el input cuando el campo sea obligatorio. */
    required?: boolean;
    error?: string;
    /** Contenido alineado a la derecha en la fila del label (p. ej. enlace "Descargar"). */
    labelAccessory?: ReactNode;
    containerClassName?: string;
    inputClassName?: string;
    labelClassName?: string;
    errorClassName?: string;
    inputProps?: Omit<ComponentProps<'input'>, 'type'>;
};
