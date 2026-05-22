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
                'block border-b border-border/60 px-4 py-3 transition-colors hover:bg-muted/50',
                isSelected && 'bg-muted',
            )}
        >
            <div className="flex items-start justify-between gap-2">
                <p className="truncate text-sm font-medium">{item.contact_name}</p>
                <span className="shrink-0 text-[10px] text-muted-foreground">
                    {activityLabel}
                </span>
            </div>
            <div className="mt-1 flex flex-wrap items-center gap-1.5">
                <Badge variant={contactTypeBadgeVariant(item.contact_type)}>
                    {contactTypeLabel(item)}
                </Badge>
                <SourceBadge source={item.source} showLabel={false} />
            </div>
            {/* {item.last_message_preview ? (
                <p className="mt-1 line-clamp-2 text-xs text-muted-foreground">
                    {item.last_message_preview}
                </p>
            ) : null} */}
        </Link>
    );
}
