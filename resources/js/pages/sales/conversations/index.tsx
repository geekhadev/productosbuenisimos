import { Head, usePage } from '@inertiajs/react';
import { ConversationDetailPanel } from '@/pages/sales/conversations/conversation-detail';
import { ConversationList } from '@/pages/sales/conversations/conversation-list';
import type { ConversationsIndexPageProps } from '@/pages/sales/conversations/types';
import { dashboard } from '@/routes';
import { index as conversationsIndex } from '@/routes/sales/conversations';

function ConversationsIndex() {
    const { conversations, selected } = usePage()
        .props as unknown as ConversationsIndexPageProps;

    const selectedId = selected?.id ?? null;
    const showDetailOnMobile = selectedId != null;

    return (
        <>
            <Head title="Conversaciones" />

            <div className="flex h-[calc(100svh-100px)] min-h-0 flex-col overflow-hidden mt-2">
                <div className="flex min-h-0 flex-1 overflow-hidden bg-card">
                    <aside
                        className={[
                            'flex h-full min-h-0 w-full shrink-0 flex-col border-border/60 lg:w-80 lg:border-r xl:w-96',
                            showDetailOnMobile ? 'hidden lg:flex' : 'flex',
                        ].join(' ')}
                    >
                        <ConversationList
                            conversations={conversations}
                            selectedId={selectedId}
                        />
                    </aside>

                    <section
                        className={[
                            'flex h-full min-h-0 min-w-0 flex-1 flex-col',
                            showDetailOnMobile ? 'flex' : 'hidden lg:flex',
                        ].join(' ')}
                    >
                        <ConversationDetailPanel
                            detail={selected}
                            showBackButton={showDetailOnMobile}
                        />
                    </section>
                </div>
            </div>
        </>
    );
}

ConversationsIndex.layout = {
    breadcrumbs: [
        { title: 'Panel', href: dashboard() },
        { title: 'Conversaciones', href: conversationsIndex() },
    ],
};

export default ConversationsIndex;
