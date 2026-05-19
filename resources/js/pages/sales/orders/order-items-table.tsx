import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { computeLineTotal } from '@/pages/sales/orders/hooks/use-order-form';
import type { OrderItemFormLine } from '@/pages/sales/orders/types';

type OrderItemsTableProps = {
    items: OrderItemFormLine[];
    totalAmount: string;
    disabled?: boolean;
    errors: Record<string, string | undefined>;
    onUpdateLine: (
        index: number,
        patch: Partial<Pick<OrderItemFormLine, 'quantity' | 'unit_price'>>,
    ) => void;
};

export function OrderItemsTable({
    items,
    totalAmount,
    disabled = false,
    errors,
    onUpdateLine,
}: OrderItemsTableProps) {
    return (
        <div className="border-border space-y-3 rounded-lg border">
            <div className="border-border border-b px-3 py-2">
                <h2 className="text-base font-semibold tracking-tight">Detalle del pedido</h2>
                <p className="text-muted-foreground text-xs">
                    Ajusta cantidad y precio unitario por línea. El subtotal se calcula al guardar.
                </p>
            </div>
            {items.length === 0 ? (
                <p className="text-muted-foreground px-3 py-4 text-center text-sm">
                    Este pedido no tiene líneas de detalle.
                </p>
            ) : (
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Producto</TableHead>
                            <TableHead className="w-28 text-right">Cantidad</TableHead>
                            <TableHead className="w-32 text-right">Precio unit.</TableHead>
                            <TableHead className="w-28 text-right">Subtotal</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {items.map((item, index) => {
                            const lineTotal = computeLineTotal(item.quantity, item.unit_price);
                            const quantityError = errors[`items.${index}.quantity`];
                            const unitPriceError = errors[`items.${index}.unit_price`];

                            return (
                                <TableRow key={item.product_id}>
                                    <TableCell>
                                        <div className="font-medium">{item.product_name}</div>
                                        <p className="text-muted-foreground text-xs">
                                            {item.product_code}
                                            {item.product_sku ? ` · ${item.product_sku}` : ''}
                                        </p>
                                    </TableCell>
                                    <TableCell className="align-top">
                                        <Input
                                            type="number"
                                            min={1}
                                            step={1}
                                            inputMode="numeric"
                                            disabled={disabled}
                                            className="h-9 text-right tabular-nums"
                                            id={`order-item-${index}-quantity`}
                                            name={`items[${index}][quantity]`}
                                            value={item.quantity}
                                            onChange={(e) =>
                                                onUpdateLine(index, { quantity: e.target.value })
                                            }
                                            aria-invalid={quantityError != null}
                                        />
                                        <InputError message={quantityError} className="mt-1" />
                                    </TableCell>
                                    <TableCell className="align-top">
                                        <Input
                                            type="number"
                                            min={0}
                                            step="0.01"
                                            inputMode="decimal"
                                            disabled={disabled}
                                            className="h-9 text-right tabular-nums"
                                            id={`order-item-${index}-unit_price`}
                                            name={`items[${index}][unit_price]`}
                                            value={item.unit_price}
                                            onChange={(e) =>
                                                onUpdateLine(index, { unit_price: e.target.value })
                                            }
                                            aria-invalid={unitPriceError != null}
                                        />
                                        <input
                                            type="hidden"
                                            name={`items[${index}][product_id]`}
                                            value={item.product_id}
                                        />
                                        <InputError message={unitPriceError} className="mt-1" />
                                    </TableCell>
                                    <TableCell className="pt-3 text-right align-top tabular-nums">
                                        {lineTotal}
                                    </TableCell>
                                </TableRow>
                            );
                        })}
                        <TableRow>
                            <TableCell colSpan={3} className="text-right font-semibold">
                                Total
                            </TableCell>
                            <TableCell className="text-right font-semibold tabular-nums">
                                {totalAmount}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            )}
        </div>
    );
}
