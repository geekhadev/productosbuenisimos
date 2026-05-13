import type { ComponentProps } from 'react';

export type FormSelectOption = {
    id: string | number;
    label: string;
};

export type FormSelectProps = {
    options: FormSelectOption[];
    /** Texto de la primera opción con `value=""` (vacío). No cuenta como valor válido si el select es `required`. */
    placeholder?: string;
    label?: string;
    /** Muestra " (*)" en el label; debe coincidir con `required` en el select cuando el campo sea obligatorio. */
    required?: boolean;
    error?: string;
    containerClassName?: string;
    selectClassName?: string;
    labelClassName?: string;
    errorClassName?: string;
    selectProps?: ComponentProps<'select'>;
};
