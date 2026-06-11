const COLOR_HEX: Record<string, string> = {
    'red-500': '#ef4444',
    'orange-500': '#f97316',
    'amber-500': '#f59e0b',
    'yellow-500': '#eab308',
    'lime-500': '#84cc16',
    'green-500': '#22c55e',
    'emerald-500': '#10b981',
    'teal-500': '#14b8a6',
    'cyan-500': '#06b6d4',
    'sky-500': '#0ea5e9',
    'blue-500': '#3b82f6',
    'indigo-500': '#6366f1',
    'violet-500': '#8b5cf6',
    'purple-500': '#a855f7',
    'fuchsia-500': '#d946ef',
    'pink-500': '#ec4899',
    'rose-500': '#f43f5e',
    'slate-500': '#64748b',
    'gray-500': '#6b7280',
    'zinc-500': '#71717a',
};

import type { ConversationTagSummary } from '@/pages/sales/conversations/types';

type ConversationTagBadgeProps = {
    tag: ConversationTagSummary;
    className?: string;
};

export function ConversationTagBadge({
    tag,
    className,
}: ConversationTagBadgeProps) {
    const hex = COLOR_HEX[tag.color] ?? '#71717a';

    return (
        <span
            className={`inline-flex h-5 shrink-0 items-center rounded-md border px-1.5 text-[10px] leading-none font-normal ${className ?? ''}`}
            style={{ borderColor: hex, color: hex }}
        >
            {tag.name}
        </span>
    );
}
