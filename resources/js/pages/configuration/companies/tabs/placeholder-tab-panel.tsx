import { Building2 } from 'lucide-react';

type PlaceholderTabPanelProps = {
    message: string;
};

export function PlaceholderTabPanel({ message }: PlaceholderTabPanelProps) {
    return (
        <div className="text-muted-foreground flex w-full items-start gap-3 rounded-lg border border-dashed p-6 text-sm">
            <Building2 className="size-5 shrink-0" />
            <p>{message}</p>
        </div>
    );
}
