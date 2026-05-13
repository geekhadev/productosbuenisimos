export type FormDateRangeFieldProps = {
    /** Valor inferior del rango (`yyyy-MM-dd`). */
    fromValue: string;
    /** Valor superior del rango (`yyyy-MM-dd`). */
    toValue: string;
    /** Se llama con cadenas vacías al limpiar la selección. */
    onRangeChange: (from: string, to: string) => void;
    label?: string;
    /** Si se omite, se genera con `useId`. */
    id?: string;
    placeholder?: string;
    containerClassName?: string;
    labelClassName?: string;
    /** Clases extra del contenido del popover (p. ej. `z-[60]` dentro de otro overlay). */
    popoverContentClassName?: string;
};
