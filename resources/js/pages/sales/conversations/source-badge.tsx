import { Camera, Facebook, Globe, MessageCircle, Share2 } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import { CHATBOT_SOURCE_LABELS } from '@/pages/sales/conversations/labels';

const SOURCE_CONFIG: Record<
    string,
    { icon: LucideIcon; className: string }
> = {
    web: {
        icon: Globe,
        className:
            'border-border bg-muted text-muted-foreground hover:bg-muted',
    },
    whatsapp: {
        icon: MessageCircle,
        className:
            'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200',
    },
    facebook: {
        icon: Facebook,
        className:
            'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-200',
    },
    instagram: {
        icon: Camera,
        className:
            'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-800 dark:border-fuchsia-900 dark:bg-fuchsia-950 dark:text-fuchsia-200',
    },
};

type SourceBadgeProps = {
    source: string;
    className?: string;
    showLabel?: boolean;
};

export function SourceBadge({
    source,
    className,
    showLabel = true,
}: SourceBadgeProps) {
    const config = SOURCE_CONFIG[source] ?? {
        icon: Share2,
        className: 'border-border bg-muted text-muted-foreground',
    };
    const Icon = config.icon;
    const label = CHATBOT_SOURCE_LABELS[source] ?? source;

    return (
        <Badge
            variant="outline"
            className={cn('gap-1 font-normal', config.className, className)}
        >
            <Icon className="size-3 shrink-0" aria-hidden />
            {showLabel ? <span>{label}</span> : null}
        </Badge>
    );
}
