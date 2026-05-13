export type DateDisplayMode = 'date' | 'datetime' | 'time';

export type DateDisplayProps = {
    value: string | Date | null | undefined;
    mode: DateDisplayMode;
    /** Texto cuando no hay valor o la fecha es inválida. */
    empty?: string;
    /**
     * `time`: elemento `<time dateTime>` cuando hay fecha válida.
     * `span`: solo texto.
     */
    as?: 'time' | 'span';
    className?: string;
};
