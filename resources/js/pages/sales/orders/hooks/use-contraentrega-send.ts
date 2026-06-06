import { useCallback, useState } from 'react';
import { toast } from 'sonner';
import { jsonFetchHeaders } from '@/lib/json-fetch-headers';

type SendResult = {
    order_id: string;
    name: string;
    success: boolean;
    error: string | null;
};

type SendResponse = {
    results: SendResult[];
    error?: string;
};

export function useContraEntregaSend() {
    const [selectedIds, setSelectedIds] = useState<Set<string>>(new Set());
    const [isSending, setIsSending] = useState(false);

    const toggleId = useCallback((id: string) => {
        setSelectedIds((prev) => {
            const next = new Set(prev);
            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }
            return next;
        });
    }, []);

    const toggleAll = useCallback((ids: string[]) => {
        setSelectedIds((prev) => {
            const allSelected = ids.every((id) => prev.has(id));
            if (allSelected) {
                const next = new Set(prev);
                ids.forEach((id) => next.delete(id));
                return next;
            }
            return new Set([...prev, ...ids]);
        });
    }, []);

    const clearSelection = useCallback(() => {
        setSelectedIds(new Set());
    }, []);

    const sendToContraEntrega = useCallback(async () => {
        if (selectedIds.size === 0 || isSending) {
            return;
        }

        setIsSending(true);

        try {
            const res = await fetch('/sales/orders/fulfillment/send-to-contraentrega', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    ...jsonFetchHeaders(),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ order_ids: [...selectedIds] }),
            });

            const json = (await res.json().catch(() => ({} as SendResponse))) as SendResponse;

            if (!res.ok) {
                toast.error(json.error ?? 'Error al enviar los pedidos a ContraEntrega.');
                return;
            }

            const results = json.results ?? [];
            const succeeded = results.filter((r) => r.success);
            const failed = results.filter((r) => !r.success);

            if (succeeded.length > 0) {
                toast.success(
                    succeeded.length === 1
                        ? `Pedido «${succeeded[0].name}» enviado a ContraEntrega.`
                        : `${succeeded.length} pedidos enviados a ContraEntrega.`,
                );
            }

            failed.forEach((r) => {
                toast.error(`«${r.name}»: ${r.error ?? 'Error desconocido.'}`);
            });

            if (succeeded.length > 0) {
                clearSelection();
            }
        } catch {
            toast.error('No se pudo conectar con el servidor. Intenta de nuevo.');
        } finally {
            setIsSending(false);
        }
    }, [selectedIds, isSending, clearSelection]);

    return {
        selectedIds,
        isSending,
        toggleId,
        toggleAll,
        clearSelection,
        sendToContraEntrega,
    };
}
