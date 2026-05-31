import { Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import {
    CONTACT_TYPE_LABELS,
    LEAD_STATUS_LABELS,
} from '@/pages/sales/conversations/labels';
import { SourceBadge } from '@/pages/sales/conversations/source-badge';
import type { ConversationListItem } from '@/pages/sales/conversations/types';
import { show as conversationShow } from '@/routes/sales/conversations';

type ConversationItemProps = {
    item: ConversationListItem;
    isSelected: boolean;
};

function contactTypeBadgeVariant(
    contactType: ConversationListItem['contact_type'],
): 'default' | 'secondary' | 'outline' {
    switch (contactType) {
        case 'customer':
            return 'default';
        case 'lead':
            return 'secondary';
        default:
            return 'outline';
    }
}

function contactTypeLabel(item: ConversationListItem): string {
    if (item.contact_type === 'lead' && item.lead_status) {
        const status =
            LEAD_STATUS_LABELS[item.lead_status] ?? item.lead_status;

        return `Lead · ${status}`;
    }

    return CONTACT_TYPE_LABELS[item.contact_type] ?? item.contact_type;
}

const listBadgeClass =
    'h-5 shrink-0 px-1.5 text-[10px] leading-none font-normal';

export function ConversationItem({ item, isSelected }: ConversationItemProps) {
    const activityLabel = new Date(item.last_activity_at).toLocaleString([], {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });

    return (
        <Link
            href={conversationShow.url(item.id)}
            preserveScroll
            className={cn(
                'flex items-center gap-2 border-b border-border/60 px-2 py-2 transition-colors hover:bg-muted/50',
                isSelected && 'bg-muted',
            )}
        >
            <p className="min-w-0 flex items-center gap-2 flex-1 truncate text-sm font-medium">
                <Badge
                    variant={contactTypeBadgeVariant(item.contact_type)}
                    className={listBadgeClass}
                >
                    {contactTypeLabel(item)}
                </Badge>
                {item.contact_name}
            </p>
            <div className="flex shrink-0 items-center gap-1">
                <SourceBadge
                    source={item.source}
                    showLabel={false}
                    className={cn(listBadgeClass, 'w-5 justify-center px-0')}
                />
            </div>
            <span className="shrink-0 text-[10px] tabular-nums text-muted-foreground">
                {activityLabel}
            </span>
        </Link>
    );
}
