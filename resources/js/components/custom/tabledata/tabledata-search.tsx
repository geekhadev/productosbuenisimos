import { FormTextInput } from '@/components/custom/form-text-input';
import {
    TABLEDATA_SEARCH_DEFAULT_PLACEHOLDER,
    TABLEDATA_SEARCH_INPUT_ID,
} from '@/components/custom/tabledata/config';
import { cn } from '@/lib/utils';

export type TabledataSearchProps = {
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    id?: string;
    containerClassName?: string;
};

/**
 * Campo de búsqueda reutilizable para listados con tabla (valor controlado; el debounce vive en el hook/página).
 */
export function TabledataSearch({
    value,
    onChange,
    placeholder = TABLEDATA_SEARCH_DEFAULT_PLACEHOLDER,
    id = TABLEDATA_SEARCH_INPUT_ID,
    containerClassName,
}: TabledataSearchProps) {
    return (
        <FormTextInput
            type="search"
            placeholder={placeholder}
            containerClassName={cn('w-full', containerClassName)}
            inputProps={{
                id,
                value,
                onChange: (e) => onChange(e.target.value),
                autoComplete: 'off',
            }}
        />
    );
}
