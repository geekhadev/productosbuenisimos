import { useHttp } from '@inertiajs/react';
import { useCallback, useState } from 'react';
import { iniciar, mensaje, nuevaConversacion } from '@/actions/App/Http/Controllers/Public/ChatbotController';
import type {
    ChatbotIniciarResponse,
    ChatbotMessage,
    ChatbotPhase,
    ChatbotProductContext,
} from '@/components/chatbot/types';

const STORAGE_PHONE_KEY = 'chatbot_phone';
const STORAGE_CONVERSATION_KEY = 'chatbot_conversation_id';

const CHATBOT_SOURCE = 'web' as const;

type UseChatbotOptions = {
    product?: ChatbotProductContext;
};

type PostRoute = {
    url: string;
    method: 'post';
};

export function useChatbot({ product }: UseChatbotOptions = {}) {
    const { submit, transform } = useHttp();

    const [phase, setPhase] = useState<ChatbotPhase>('phone');
    const [messages, setMessages] = useState<ChatbotMessage[]>([]);
    const [conversationId, setConversationId] = useState<string | null>(null);
    const [isNew, setIsNew] = useState(false);
    const [isSending, setIsSending] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const postJson = useCallback(
        async <T>(route: PostRoute, body: Record<string, unknown>): Promise<T> => {
            transform(() => body);

            return (await submit(route)) as T;
        },
        [submit, transform],
    );

    const persistSession = useCallback((phone: string, id: string) => {
        localStorage.setItem(STORAGE_PHONE_KEY, phone);
        localStorage.setItem(STORAGE_CONVERSATION_KEY, id);
    }, []);

    const clearSession = useCallback(() => {
        localStorage.removeItem(STORAGE_PHONE_KEY);
        localStorage.removeItem(STORAGE_CONVERSATION_KEY);
        setConversationId(null);
        setMessages([]);
        setIsNew(false);
        setError(null);
        setPhase('phone');
    }, []);

    const applyIniciarResponse = useCallback(
        (phone: string, data: ChatbotIniciarResponse) => {
            setConversationId(data.conversation_id);
            setMessages(data.messages);
            setIsNew(data.is_new);
            persistSession(phone, data.conversation_id);
            setPhase('chat');
        },
        [persistSession],
    );

    const iniciarChat = useCallback(
        async (phone: string) => {
            setError(null);
            setPhase('loading');

            try {
                const data = await postJson<ChatbotIniciarResponse>(iniciar.post(), {
                    phone,
                    source: CHATBOT_SOURCE,
                });

                applyIniciarResponse(phone, data);
            } catch {
                setError('No pudimos conectar con el chat. Revisa tu conexión e inténtalo de nuevo.');
                setPhase('phone');
            }
        },
        [applyIniciarResponse, postJson],
    );

    const resumeFromStorage = useCallback(async () => {
        const phone = localStorage.getItem(STORAGE_PHONE_KEY);

        if (!phone) {
            return;
        }

        await iniciarChat(phone);
    }, [iniciarChat]);

    const enviar = useCallback(
        async (message: string) => {
            if (!conversationId || message.trim() === '') {
                return;
            }

            const trimmed = message.trim();
            const optimisticMessage: ChatbotMessage = {
                role: 'user',
                source: CHATBOT_SOURCE,
                content: trimmed,
                created_at: new Date().toISOString(),
            };

            setMessages((current) => [...current, optimisticMessage]);
            setIsSending(true);
            setError(null);

            const shouldSendProductContext = isNew && product != null;

            try {
                const data = await postJson<{ reply: string }>(mensaje.post(), {
                    conversation_id: conversationId,
                    source: CHATBOT_SOURCE,
                    message: trimmed,
                    ...(shouldSendProductContext ? { product_context: product } : {}),
                });

                if (shouldSendProductContext) {
                    setIsNew(false);
                }

                setMessages((current) => [
                    ...current,
                    {
                        role: 'assistant',
                        source: CHATBOT_SOURCE,
                        content: data.reply,
                        created_at: new Date().toISOString(),
                    },
                ]);
            } catch {
                setMessages((current) => current.filter((item) => item !== optimisticMessage));
                setError('No pudimos enviar tu mensaje. Inténtalo de nuevo.');
            } finally {
                setIsSending(false);
            }
        },
        [conversationId, isNew, postJson, product],
    );

    const nuevaConversacionChat = useCallback(async () => {
        if (!conversationId) {
            return;
        }

        setIsSending(true);
        setError(null);

        try {
            const data = await postJson<ChatbotIniciarResponse>(nuevaConversacion.post(), {
                conversation_id: conversationId,
                source: CHATBOT_SOURCE,
            });

            setConversationId(data.conversation_id);
            setMessages([]);
            setIsNew(true);

            const phone = localStorage.getItem(STORAGE_PHONE_KEY);

            if (phone) {
                persistSession(phone, data.conversation_id);
            }
        } catch {
            setError('No pudimos iniciar una nueva conversación. Inténtalo de nuevo.');
        } finally {
            setIsSending(false);
        }
    }, [conversationId, persistSession, postJson]);

    const cerrarSesion = useCallback(() => {
        clearSession();
    }, [clearSession]);

    return {
        phase,
        messages,
        conversationId,
        isNew,
        isSending,
        error,
        iniciarChat,
        resumeFromStorage,
        enviar,
        nuevaConversacionChat,
        cerrarSesion,
        setError,
    };
}
