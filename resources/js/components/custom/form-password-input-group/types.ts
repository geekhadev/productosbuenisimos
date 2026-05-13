import type { ComponentProps } from 'react';

export type FormPasswordInputGroupProps = {
    label?: string;
    /** Muestra " (*)" en el label; debe coincidir con `required` en el input cuando el campo sea obligatorio. */
    required?: boolean;
    error?: string;
    containerClassName?: string;
    inputClassName?: string;
    labelClassName?: string;
    errorClassName?: string;
    placeholder?: string;
    inputProps?: Omit<ComponentProps<'input'>, 'type'>;
};
