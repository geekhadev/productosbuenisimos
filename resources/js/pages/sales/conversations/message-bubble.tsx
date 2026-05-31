import { cn } from '@/lib/utils';
import { SourceBadge } from '@/pages/sales/conversations/source-badge';
import type { ConversationMessage, ConversationVideoAttachment } from '@/pages/sales/conversations/types';

type MessageBubbleProps = {
    message: ConversationMessage;
    conversationSource: string;
};

export function MessageBubble({
    message,
    conversationSource,
}: MessageBubbleProps) {
    const isUser = message.role === 'user';
    const showSource = message.source !== conversationSource;
    const timeLabel =
        message.created_at != null
            ? new Date(message.created_at).toLocaleTimeString([], {
                  hour: '2-digit',
                  minute: '2-digit',
              })
            : '';

    return (
        <div
            className={cn(
                'flex flex-col gap-1',
                isUser ? 'items-end' : 'items-start',
            )}
        >
            <div
                className={cn(
                    'flex max-w-[85%] flex-col gap-1',
                    isUser ? 'items-end' : 'items-start',
                )}
            >
                {showSource ? (
                    <SourceBadge source={message.source} className="text-xs" />
                ) : null}
                <div
                    className={cn(
                        'rounded-2xl px-3 py-2 text-sm whitespace-pre-wrap',
                        isUser
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-muted text-foreground',
                    )}
                >
                    {message.content}
                    {message.attachments?.map((attachment, attachmentIndex) => (
                        <ConversationAttachment
                            attachment={attachment}
                            key={`${message.id}-attachment-${attachmentIndex}`}
                        />
                    ))}
                </div>
            </div>
            <div className="flex items-center gap-2 px-1">
                {timeLabel ? (
                    <span className="text-[10px] text-muted-foreground">
                        {timeLabel}
                    </span>
                ) : null}
                {(message.input_tokens != null || message.output_tokens != null) ? (
                    <span className="text-[10px] text-muted-foreground/60">
                        {[
                            message.input_tokens != null && `↑${message.input_tokens.toLocaleString()}`,
                            message.output_tokens != null && `↓${message.output_tokens.toLocaleString()}`,
                        ].filter(Boolean).join(' ')} tok
                    </span>
                ) : null}
            </div>
        </div>
    );
}

function ConversationAttachment({
    attachment,
}: {
    attachment: ConversationVideoAttachment;
}) {
    if (attachment.type !== 'video') {
        return null;
    }

    return (
        <div className="mt-2 space-y-1">
            <p className="text-xs font-medium opacity-80">{attachment.product_name}</p>
            <video
                className="max-h-64 w-full rounded-lg bg-black/10"
                controls
                playsInline
                preload="metadata"
                src={attachment.url}
            >
                Tu navegador no puede reproducir este video.
            </video>
        </div>
    );
}
