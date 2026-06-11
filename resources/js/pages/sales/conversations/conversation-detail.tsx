import { router } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { ArrowLeft, Download, ExternalLink, Send, Trash2 } from 'lucide-react';
import {  useEffect, useRef, useState } from 'react';
import type {FormEvent} from 'react';
import { WhatsAppIcon } from '@/components/custom/whatsapp-icon';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import {
    CONTACT_TYPE_LABELS,
    LEAD_STATUS_LABELS,
} from '@/pages/sales/conversations/labels';
import { MessageBubble } from '@/pages/sales/conversations/message-bubble';
import { SourceBadge } from '@/pages/sales/conversations/source-badge';
import type { ConversationDetail } from '@/pages/sales/conversations/types';
import {
    destroy as conversationDestroy,
    downloadTxt as conversationDownloadTxt,
    index as conversationsIndex,
} from '@/routes/sales/conversations';
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
    const [operatorMessage, setOperatorMessage] = useState('');
    const [isSending, setIsSending] = useState(false);

    // Auto-scroll al último mensaje
    useEffect(() => {
        const container = messagesRef.current;

        if (detail == null || container == null) {
            return;
        }

        container.scrollTop = container.scrollHeight;
    }, [detail]);

    // Polling cada 10 segundos
    useEffect(() => {
        if (detail == null) {
return;
}

        const interval = setInterval(() => {
            router.reload({ only: ['selected'], preserveScroll: true });
        }, 10000);

        return () => clearInterval(interval);
    }, [detail?.id]);

    if (detail == null) {
        return (
            <div className="flex h-full min-h-0 flex-1 items-center justify-center p-6">
                <p className="text-center text-sm text-muted-foreground">
                    Selecciona una conversación para ver el historial de mensajes.
                </p>
            </div>
        );
    }

    function handleDelete() {
        router.delete(conversationDestroy.url(detail!.id));
    }

    function handleToggleAgent() {
        router.patch(
            `/sales/conversations/${detail!.id}/toggle-agent`,
            {},
            { preserveScroll: true },
        );
    }

    function handleSendOperatorMessage(e: FormEvent) {
        e.preventDefault();

        if (!operatorMessage.trim() || isSending) {
return;
}

        setIsSending(true);

        router.post(
            `/sales/conversations/${detail!.id}/operator-message`,
            { message: operatorMessage },
            {
                preserveScroll: true,
                onSuccess: () => setOperatorMessage(''),
                onFinish: () => setIsSending(false),
            },
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
                        <div className="flex items-center gap-1.5">
                            <p className="truncate text-xs text-muted-foreground">
                                {detail.phone}
                            </p>
                            {detail.phone ? (
                                <a
                                    href={`https://wa.me/${detail.phone.replace(/\D/g, '')}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label="Abrir WhatsApp"
                                    className="shrink-0 text-[#25D366]"
                                >
                                    <WhatsAppIcon className="size-3.5" />
                                </a>
                            ) : null}
                        </div>
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
                    <div className="flex shrink-0 flex-col items-end gap-1">
                        <label className="flex cursor-pointer items-center gap-2 text-xs text-muted-foreground">
                            Agente
                            <Switch
                                checked={!detail.agent_paused}
                                onCheckedChange={handleToggleAgent}
                            />
                        </label>
                        {detail.agent_paused ? (
                            <span className="text-xs font-medium text-amber-600">
                                Modo operador
                            </span>
                        ) : null}
                        <div className="flex items-center gap-0.5">
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                className="size-7 text-muted-foreground"
                                aria-label="Descargar conversación en TXT"
                                asChild
                            >
                                <a href={conversationDownloadTxt.url(detail.id)}>
                                    <Download className="size-3.5" />
                                </a>
                            </Button>
                            <AlertDialog>
                            <AlertDialogTrigger asChild>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon"
                                    className="size-7 text-muted-foreground hover:text-destructive"
                                    aria-label="Eliminar conversación"
                                >
                                    <Trash2 className="size-3.5" />
                                </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                                <AlertDialogHeader>
                                    <AlertDialogTitle>¿Eliminar conversación?</AlertDialogTitle>
                                    <AlertDialogDescription>
                                        Se eliminarán todos los mensajes e historial del agente de forma permanente. Esta acción no se puede deshacer.
                                    </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                                    <AlertDialogAction
                                        onClick={handleDelete}
                                        className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                                    >
                                        Eliminar
                                    </AlertDialogAction>
                                </AlertDialogFooter>
                            </AlertDialogContent>
                        </AlertDialog>
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

            {detail.agent_paused ? (
                <form
                    onSubmit={handleSendOperatorMessage}
                    className="shrink-0 border-t border-border/60 px-4 py-3"
                >
                    <div className="flex gap-2">
                        <textarea
                            value={operatorMessage}
                            onChange={(e) => setOperatorMessage(e.target.value)}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter' && !e.shiftKey) {
                                    e.preventDefault();
                                    handleSendOperatorMessage(e as unknown as FormEvent);
                                }
                            }}
                            placeholder="Escribe un mensaje como operador..."
                            rows={2}
                            className="min-h-0 flex-1 resize-none rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            disabled={isSending}
                        />
                        <Button
                            type="submit"
                            size="icon"
                            disabled={!operatorMessage.trim() || isSending}
                            className="self-end"
                        >
                            <Send className="size-4" />
                        </Button>
                    </div>
                    <p className="mt-1 text-xs text-muted-foreground">
                        Enter para enviar · Shift+Enter para nueva línea
                    </p>
                </form>
            ) : null}
        </div>
    );
}
