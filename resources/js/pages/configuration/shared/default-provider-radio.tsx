import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type DefaultProviderRadioProps = {
    slug: string;
    label: string;
    name: string;
    checked: boolean;
    disabled: boolean;
    onSelect: (slug: string) => void;
};

export function DefaultProviderRadio({
    slug,
    label,
    name,
    checked,
    disabled,
    onSelect,
}: DefaultProviderRadioProps) {
    const inputId = `default-provider-${slug}`;

    return (
        <div className="flex shrink-0 items-center">
            <input
                id={inputId}
                type="radio"
                name={name}
                value={slug}
                checked={checked}
                disabled={disabled}
                onChange={() => onSelect(slug)}
                className={cn(
                    'size-4 shrink-0 cursor-pointer accent-primary',
                    'disabled:cursor-not-allowed disabled:opacity-50',
                )}
            />
            <Label htmlFor={inputId} className="sr-only">
                Usar {label} como proveedor predeterminado
            </Label>
        </div>
    );
}
