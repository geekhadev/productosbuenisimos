import type { Paginated } from '@/types/pagination';

export type ConversationListItem = {
    id: string;
    phone: string;
    contact_name: string;
    contact_type: 'customer' | 'lead' | 'unknown';
    lead_status: string | null;
    customer_id: string | null;
    source: string;
    last_message_preview: string | null;
    last_activity_at: string;
};

export type ConversationVideoAttachment = {
    type: 'video';
    url: string;
    product_name: string;
};

export type ConversationMessage = {
    id: string;
    role: 'user' | 'assistant';
    source: string;
    content: string;
    attachments?: ConversationVideoAttachment[];
    input_tokens: number | null;
    output_tokens: number | null;
    created_at: string;
};

export type ConversationDetail = {
    id: string;
    phone: string;
    agent_paused: boolean;
    contact_name: string;
    contact_type: 'customer' | 'lead' | 'unknown';
    lead_status: string | null;
    customer_id: string | null;
    source: string;
    messages: ConversationMessage[];
    total_input_tokens: number;
    total_output_tokens: number;
};

export type ConversationsIndexPageProps = {
    conversations: Paginated<ConversationListItem>;
    selected: ConversationDetail | null;
};
