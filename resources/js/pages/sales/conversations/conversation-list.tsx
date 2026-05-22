import { TabledataPagination } from '@/components/custom/tabledata';
import { ConversationItem } from '@/pages/sales/conversations/conversation-item';
import type {
    ConversationListItem,
    ConversationsIndexPageProps,
} from '@/pages/sales/conversations/types';

type ConversationListProps = {
    conversations: ConversationsIndexPageProps['conversations'];
    selectedId: string | null;
};

export function ConversationList({
    conversations,
    selectedId,
}: ConversationListProps) {
    return (
        <div className="flex min-h-0 flex-1 flex-col">
            <div className="min-h-0 flex-1 overflow-y-auto overscroll-contain">
                {conversations.data.length === 0 ? (
                    <p className="px-4 py-8 text-center text-sm text-muted-foreground">
                        No hay conversaciones registradas.
                    </p>
                ) : (
                    conversations.data.map((item: ConversationListItem) => (
                        <ConversationItem
                            key={item.id}
                            item={item}
                            isSelected={item.id === selectedId}
                        />
                    ))
                )}
            </div>
            <div className="shrink-0 border-t border-border/60 p-3">
                <TabledataPagination
                    paginator={conversations}
                    only={['conversations', 'selected']}
                />
            </div>
        </div>
    );
}
