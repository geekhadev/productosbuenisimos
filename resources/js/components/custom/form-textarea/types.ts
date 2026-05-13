import type { ComponentProps } from 'react';

export type FormTextareaProps = {
    placeholder?: string;
    label?: string;
    /** Muestra " (*)" en el label; debe coincidir con `required` en el textarea cuando el campo sea obligatorio. */
    required?: boolean;
    error?: string;
    containerClassName?: string;
    textareaClassName?: string;
    labelClassName?: string;
    errorClassName?: string;
    textareaProps?: ComponentProps<'textarea'>;
};
