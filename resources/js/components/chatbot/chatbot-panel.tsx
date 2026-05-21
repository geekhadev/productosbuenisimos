import { Loader2, MessageSquarePlus, Send, UserX } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { ChatbotMessage } from '@/components/chatbot/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

const SOURCE_LABELS: Record<ChatbotMessage['source'], string> = {
    web: 'Web',
    whatsapp: 'WhatsApp',
    facebook: 'Facebook',
    instagram: 'Instagram',
};

type ChatbotPanelProps = {
    messages: ChatbotMessage[];
    isSending: boolean;
    error: string | null;
    onSend: (message: string) => void;
    onNewConversation: () => void;
    onSignOut: () => void;
};

export function ChatbotPanel({
    messages,
    isSending,
    error,
    onSend,
    onNewConversation,
    onSignOut,
}: ChatbotPanelProps) {
    const [draft, setDraft] = useState('');
    const bottomRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages, isSending]);

    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        if (draft.trim() === '' || isSending) {
            return;
        }

        onSend(draft);
        setDraft('');
    };

    return (
        <div className="flex h-full min-h-0 flex-col">
            <header className="flex items-center justify-between gap-2 border-b border-border/60 px-4 py-3">
                <div>
                    <h2 className="text-base font-semibold">Asistente de ventas</h2>
                    <p className="text-xs text-muted-foreground">Te ayudamos con tu pedido</p>
                </div>
                <div className="flex shrink-0 gap-1">
                    <Button
                        aria-label="Nueva conversación"
                        disabled={isSending}
                        onClick={onNewConversation}
                        size="icon"
                        type="button"
                        variant="ghost"
                    >
                        <MessageSquarePlus className="size-4" />
                    </Button>
                    <Button
                        aria-label="No soy yo"
                        onClick={onSignOut}
                        size="icon"
                        type="button"
                        variant="ghost"
                    >
                        <UserX className="size-4" />
                    </Button>
                </div>
            </header>

            <div className="min-h-0 flex-1 space-y-3 overflow-y-auto px-4 py-4">
                {messages.length === 0 ? (
                    <p className="text-center text-sm text-muted-foreground">
                        Escribe tu mensaje para comenzar.
                    </p>
                ) : (
                    messages.map((message, index) => (
                        <ChatbotBubble key={`${message.created_at}-${index}`} message={message} />
                    ))
                )}
                {isSending ? (
                    <p className="flex items-center gap-2 text-sm text-muted-foreground">
                        <Loader2 aria-hidden className="size-4 animate-spin" />
                        El agente está escribiendo…
                    </p>
                ) : null}
                <div ref={bottomRef} />
            </div>

            {error ? <p className="px-4 text-sm text-destructive">{error}</p> : null}

            <form className="flex gap-2 border-t border-border/60 p-4" onSubmit={handleSubmit}>
                <Input
                    aria-label="Mensaje"
                    disabled={isSending}
                    maxLength={1000}
                    onChange={(event) => setDraft(event.target.value)}
                    placeholder="Escribe tu mensaje…"
                    value={draft}
                />
                <Button
                    aria-label="Enviar mensaje"
                    disabled={isSending || draft.trim() === ''}
                    size="icon"
                    type="submit"
                >
                    <Send className="size-4" />
                </Button>
            </form>
        </div>
    );
}

function ChatbotBubble({ message }: { message: ChatbotMessage }) {
    const isUser = message.role === 'user';
    const showSource = message.source !== 'web';

    return (
        <div className={cn('flex', isUser ? 'justify-end' : 'justify-start')}>
            <div
                className={cn(
                    'max-w-[85%] rounded-2xl px-3 py-2 text-sm',
                    isUser
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-muted text-foreground',
                )}
            >
                {showSource ? (
                    <p className="mb-1 text-[10px] font-medium uppercase tracking-wide opacity-70">
                        vía {SOURCE_LABELS[message.source]}
                    </p>
                ) : null}
                <p className="whitespace-pre-wrap">{message.content}</p>
            </div>
        </div>
    );
}
