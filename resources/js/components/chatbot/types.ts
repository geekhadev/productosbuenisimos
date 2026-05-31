export type ChatbotSource = 'web' | 'whatsapp' | 'facebook' | 'instagram';

export type ChatbotMessageRole = 'user' | 'assistant';

export type ChatbotVideoAttachment = {
    type: 'video';
    url: string;
    product_name: string;
};

export type ChatbotMessage = {
    role: ChatbotMessageRole;
    source: ChatbotSource;
    content: string;
    attachments?: ChatbotVideoAttachment[];
    created_at: string;
};

export type ChatbotProductContext = {
    id: string;
    name: string;
    code: string;
    sku: string;
    price: number;
};

export type ChatbotIniciarResponse = {
    conversation_id: string;
    is_new: boolean;
    messages: ChatbotMessage[];
};

export type ChatbotNuevaConversacionResponse = {
    conversation_id: string;
    is_new: boolean;
    messages: ChatbotMessage[];
};

export type ChatbotMensajeResponse = {
    reply: string;
    attachments: ChatbotVideoAttachment[];
};

export type ChatbotPhase = 'phone' | 'loading' | 'chat';
