import { Link } from '@inertiajs/react';
import { ArrowLeft, ExternalLink } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    CONTACT_TYPE_LABELS,
    LEAD_STATUS_LABELS,
} from '@/pages/sales/conversations/labels';
import { MessageBubble } from '@/pages/sales/conversations/message-bubble';
import { SourceBadge } from '@/pages/sales/conversations/source-badge';
import type { ConversationDetail } from '@/pages/sales/conversations/types';
import { index as conversationsIndex } from '@/routes/sales/conversations';
import { edit as customerEdit } from '@/routes/sales/customers';

type ConversationDetailPanelProps = {
    detail: ConversationDetail | null;
    showBackButton?: boolean;
};

function contactTypeBadgeVariant(
    contactType: ConversationDetail['contact_type'],
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

function contactTypeLabel(detail: ConversationDetail): string {
    if (detail.contact_type === 'lead' && detail.lead_status) {
        const status =
            LEAD_STATUS_LABELS[detail.lead_status] ?? detail.lead_status;

        return `Lead · ${status}`;
    }

    return CONTACT_TYPE_LABELS[detail.contact_type] ?? detail.contact_type;
}

export function ConversationDetailPanel({
    detail,
    showBackButton = false,
}: ConversationDetailPanelProps) {
    const messagesRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const container = messagesRef.current;

        if (detail == null || container == null) {
            return;
        }

        container.scrollTop = container.scrollHeight;
    }, [detail]);

    if (detail == null) {
        return (
            <div className="flex h-full min-h-0 flex-1 items-center justify-center p-6">
                <p className="text-center text-sm text-muted-foreground">
                    Selecciona una conversación para ver el historial de mensajes.
                </p>
            </div>
        );
    }

    return (
        <div className="flex h-full min-h-0 flex-1 flex-col">
            <header className="shrink-0 border-b border-border/60 px-4 py-3">
                <div className="flex items-start gap-2">
                    {showBackButton ? (
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            className="shrink-0 lg:hidden"
                            aria-label="Volver al listado"
                            asChild
                        >
                            <Link href={conversationsIndex.url()}>
                                <ArrowLeft className="size-4" />
                            </Link>
                        </Button>
                    ) : null}
                    <div className="min-w-0 flex-1">
                        <h2 className="truncate text-base font-semibold">
                            {detail.contact_name}
                        </h2>
                        <p className="truncate text-xs text-muted-foreground">
                            {detail.phone}
                        </p>
                        <div className="mt-2 flex flex-wrap items-center gap-1.5">
                            <Badge
                                variant={contactTypeBadgeVariant(
                                    detail.contact_type,
                                )}
                            >
                                {contactTypeLabel(detail)}
                            </Badge>
                            <SourceBadge source={detail.source} />
                            {(detail.total_input_tokens > 0 || detail.total_output_tokens > 0) ? (
                                <span className="text-xs text-muted-foreground">
                                    ↑{detail.total_input_tokens.toLocaleString()} ↓{detail.total_output_tokens.toLocaleString()} tok
                                </span>
                            ) : null}
                            {detail.customer_id ? (
                                <Button
                                    variant="link"
                                    size="sm"
                                    className="h-auto gap-1 px-0 text-xs"
                                    asChild
                                >
                                    <Link
                                        href={customerEdit.url(
                                            detail.customer_id,
                                        )}
                                    >
                                        Ver cliente
                                        <ExternalLink className="size-3" />
                                    </Link>
                                </Button>
                            ) : null}
                        </div>
                    </div>
                </div>
            </header>

            <div
                ref={messagesRef}
                className="min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain px-4 py-4"
            >
                {detail.messages.length === 0 ? (
                    <p className="text-center text-sm text-muted-foreground">
                        Esta conversación no tiene mensajes.
                    </p>
                ) : (
                    detail.messages.map((message) => (
                        <MessageBubble
                            key={message.id}
                            message={message}
                            conversationSource={detail.source}
                        />
                    ))
                )}
            </div>
        </div>
    );
}
