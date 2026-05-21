import { MessageCircle } from 'lucide-react';
import { useState } from 'react';
import { ChatbotPanel } from '@/components/chatbot/chatbot-panel';
import { ChatbotPhoneForm } from '@/components/chatbot/chatbot-phone-form';
import type { ChatbotProductContext } from '@/components/chatbot/types';
import { useChatbot } from '@/components/chatbot/use-chatbot';
import { Button } from '@/components/ui/button';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

type ChatbotWidgetProps = {
    product?: ChatbotProductContext;
};

export function ChatbotWidget({ product }: ChatbotWidgetProps) {
    const [open, setOpen] = useState(false);
    const [hasStoredPhone, setHasStoredPhone] = useState(
        () => typeof window !== 'undefined' && localStorage.getItem('chatbot_phone') !== null,
    );

    const chatbot = useChatbot({ product });

    const handleOpenChange = (nextOpen: boolean) => {
        setOpen(nextOpen);

        if (nextOpen && hasStoredPhone && chatbot.phase === 'phone') {
            void chatbot.resumeFromStorage();
        }
    };

    const handleSignOut = () => {
        chatbot.cerrarSesion();
        setHasStoredPhone(false);
    };

    return (
        <Popover onOpenChange={handleOpenChange} open={open}>
            <PopoverTrigger asChild>
                <Button
                    aria-expanded={open}
                    aria-label="Abrir chat de ventas"
                    className="fixed right-4 bottom-4 z-50 size-14 rounded-full shadow-lg"
                    size="icon"
                    type="button"
                >
                    <MessageCircle className="size-6" />
                </Button>
            </PopoverTrigger>

            <PopoverContent
                align="end"
                className={cn(
                    'flex w-[min(360px,calc(100vw-2rem))] flex-col gap-0 overflow-hidden p-0',
                    'h-[min(520px,calc(100dvh-7rem))] shadow-xl',
                )}
                collisionPadding={16}
                side="top"
                sideOffset={12}
            >
                {chatbot.phase === 'phone' ? (
                    <ChatbotPhoneForm
                        error={chatbot.error}
                        isLoading={false}
                        onSubmit={(phone) => {
                            setHasStoredPhone(true);
                            void chatbot.iniciarChat(phone);
                        }}
                    />
                ) : null}

                {chatbot.phase === 'loading' ? (
                    <div className="flex flex-1 items-center justify-center p-6 text-sm text-muted-foreground">
                        Cargando conversación…
                    </div>
                ) : null}

                {chatbot.phase === 'chat' ? (
                    <ChatbotPanel
                        error={chatbot.error}
                        isSending={chatbot.isSending}
                        messages={chatbot.messages}
                        onNewConversation={() => void chatbot.nuevaConversacionChat()}
                        onSend={(message) => void chatbot.enviar(message)}
                        onSignOut={handleSignOut}
                    />
                ) : null}
            </PopoverContent>
        </Popover>
    );
}
