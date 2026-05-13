import type { Matcher } from 'react-day-picker';

export type FormDatePickerFieldProps = {
    /** Valor en formato `yyyy-MM-dd` (vacío = sin fecha). */
    value: string;
    onChange: (value: string) => void;
    label?: string;
    /** Muestra " (*)" en el label; debe coincidir con `required` en el disparador cuando el campo sea obligatorio. */
    required?: boolean;
    error?: string;
    placeholder?: string;
    /** Si se omite, se genera con `useId`. */
    id?: string;
    /**
     * Si es `true`, deshabilita días posteriores al día de hoy (comparación por fecha local a medianoche).
     */
    disableFutureDates?: boolean;
    /** Matchers adicionales para pasar al `Calendar` (react-day-picker). */
    disabled?: Matcher | Matcher[];
    containerClassName?: string;
    labelClassName?: string;
    errorClassName?: string;
    triggerClassName?: string;
    /** Clases extra del contenido del popover (p. ej. `z-[60]` dentro de otro overlay). */
    popoverContentClassName?: string;
};
