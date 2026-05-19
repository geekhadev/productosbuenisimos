import { setLayoutProps, useForm } from '@inertiajs/react';
import { useCallback, useEffect, useLayoutEffect, useMemo } from 'react';
import type {
    OrderFormData,
    OrderFormRecord,
    OrderItemFormLine,
    OrdersFormPageProps,
} from '@/pages/sales/orders/types';
import { dashboard } from '@/routes';
import { edit, index as ordersIndex, update } from '@/routes/sales/orders';

function buildItemLines(order: OrderFormRecord): OrderItemFormLine[] {
    return order.items.map((item) => ({
        product_id: item.product_id,
        quantity: String(item.quantity),
        unit_price: item.unit_price,
        product_name: item.product_name,
        product_code: item.product_code,
        product_sku: item.product_sku,
    }));
}

function buildFormDefaults(order: OrderFormRecord): OrderFormData {
    return {
        name: order.name,
        customer_id: order.customer_id,
        address_id: order.address_id,
        items: buildItemLines(order),
    };
}

export function computeLineTotal(quantity: string, unitPrice: string): string {
    const q = Number.parseInt(quantity, 10);
    const p = Number.parseFloat(unitPrice);

    if (!Number.isFinite(q) || q < 1 || !Number.isFinite(p) || p < 0) {
        return '0.00';
    }

    return (q * p).toFixed(2);
}

export function computeOrderTotal(items: OrderItemFormLine[]): string {
    return items
        .reduce((sum, item) => sum + Number.parseFloat(computeLineTotal(item.quantity, item.unit_price)), 0)
        .toFixed(2);
}

export function useOrderForm({ order, customers }: OrdersFormPageProps) {
    const formDefaults = useMemo(() => buildFormDefaults(order), [order]);

    const formDefaultsSyncKey = useMemo(
        () => JSON.stringify(formDefaults),
        [formDefaults],
    );

    const form = useForm<OrderFormData>(formDefaults);
    const { setDefaults, reset, patch, setData } = form;

    useEffect(() => {
        setDefaults(formDefaults);
        reset();
    }, [formDefaultsSyncKey, formDefaults, reset, setDefaults]);

    const headTitle = `Editar pedido: ${order.name}`;

    const totalAmount = useMemo(
        () => computeOrderTotal(form.data.items),
        [form.data.items],
    );

    useLayoutEffect(() => {
        setLayoutProps({
            breadcrumbs: [
                { title: 'Panel', href: dashboard() },
                { title: 'Pedidos', href: ordersIndex() },
                { title: order.name, href: edit.url(order.id) },
            ],
        });
    }, [order.id, order.name]);

    const addressOptions = useMemo(() => {
        const customer = customers.find((c) => c.id === form.data.customer_id);

        return customer?.addresses ?? [];
    }, [customers, form.data.customer_id]);

    const customerOptions = useMemo(
        () =>
            customers.map((c) => ({
                id: c.id,
                label: `${c.full_name} (${c.phone})`,
            })),
        [customers],
    );

    const onCustomerChange = useCallback(
        (customerId: string) => {
            const customer = customers.find((c) => c.id === customerId);
            const firstAddressId = customer?.addresses[0]?.id ?? '';

            setData((data) => ({
                ...data,
                customer_id: customerId,
                address_id: firstAddressId,
            }));
        },
        [customers, setData],
    );

    const updateItemLine = useCallback(
        (index: number, patch: Partial<Pick<OrderItemFormLine, 'quantity' | 'unit_price'>>) => {
            setData((data) => ({
                ...data,
                items: data.items.map((line, i) =>
                    i === index ? { ...line, ...patch } : line,
                ),
            }));
        },
        [setData],
    );

    const submit = useCallback(
        (event: React.FormEvent) => {
            event.preventDefault();
            patch(update.url(order.id));
        },
        [order.id, patch],
    );

    return {
        form,
        submit,
        headTitle,
        customerOptions,
        addressOptions,
        onCustomerChange,
        updateItemLine,
        totalAmount,
    };
}
