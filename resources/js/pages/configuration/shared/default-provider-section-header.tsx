import InputError from '@/components/input-error';
import type { DefaultProviderOption } from '@/pages/configuration/shared/types';

type DefaultProviderSectionHeaderProps = {
    label: string;
    emptyMessage: string;
    error?: string;
    hasSelectableProviders: boolean;
};

export function DefaultProviderSectionHeader({
    label,
    emptyMessage,
    error,
    hasSelectableProviders,
}: DefaultProviderSectionHeaderProps) {
    return (
        <div className="space-y-1">
            <p className="text-sm font-medium">{label}</p>
            {!hasSelectableProviders ? (
                <p className="text-muted-foreground text-sm">{emptyMessage}</p>
            ) : null}
            <InputError message={error} />
        </div>
    );
}

export function isDefaultProviderSelectable(
    slug: string,
    options: DefaultProviderOption[],
): boolean {
    return options.some((option) => option.value === slug);
}
