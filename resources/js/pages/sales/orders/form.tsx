import { Head } from '@inertiajs/react';
import { Save, X } from 'lucide-react';
import { FormLinkButton } from '@/components/custom/form-link-button';
import { FormSelect } from '@/components/custom/form-select';
import { FormSubmitButton } from '@/components/custom/form-submit-button';
import { FormTextInput } from '@/components/custom/form-text-input';
import { useOrderForm } from '@/pages/sales/orders/hooks/use-order-form';
import { OrderItemsTable } from '@/pages/sales/orders/order-items-table';
import type { OrdersFormPageProps } from '@/pages/sales/orders/types';
import { index as ordersIndex } from '@/routes/sales/orders';

function OrderForm(props: OrdersFormPageProps) {
    const {
        form,
        submit,
        headTitle,
        customerOptions,
        addressOptions,
        onCustomerChange,
        updateItemLine,
        totalAmount,
    } = useOrderForm(props);

    return (
        <>
            <Head title={headTitle} />

            <div className="flex min-w-0 flex-1 flex-col gap-6 p-4 max-w-4xl">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">{headTitle}</h1>
                        <p className="text-muted-foreground mt-1 text-sm">
                            El nombre del pedido debe ser único dentro de la empresa.
                        </p>
                    </div>
                    <FormLinkButton
                        href={ordersIndex.url()}
                        icon={<X />}
                        label="Volver al listado"
                        buttonVariant="outline"
                        containerClassName="w-auto"
                    />
                </div>

                <form onSubmit={submit} className="space-y-6 grid grid-cols-2">
                    <FormTextInput
                        label="Nombre / referencia"
                        required
                        containerClassName="col-span-1"
                        error={form.errors.name}
                        inputProps={{
                            id: 'order-name',
                            name: 'name',
                            maxLength: 255,
                            value: form.data.name,
                            onChange: (e) => form.setData('name', e.target.value),
                        }}
                    />

                    <FormSelect
                        label="Cliente"
                        required
                        containerClassName="col-span-1"
                        error={form.errors.customer_id}
                        options={customerOptions}
                        placeholder="Seleccionar cliente"
                        selectProps={{
                            id: 'order-customer_id',
                            name: 'customer_id',
                            value: form.data.customer_id,
                            onChange: (e) => onCustomerChange(e.target.value),
                        }}
                    />
                    <FormSelect
                        label="Dirección"
                        required
                        containerClassName="col-span-2"
                        error={form.errors.address_id}
                        options={addressOptions.map((a) => ({
                            id: a.id,
                            label: a.label,
                        }))}
                        placeholder={
                            addressOptions.length === 0
                                ? 'Sin direcciones para este cliente'
                                : 'Seleccionar dirección'
                        }
                        selectProps={{
                            id: 'order-address_id',
                            name: 'address_id',
                            value: form.data.address_id,
                            disabled: addressOptions.length === 0,
                            onChange: (e) => form.setData('address_id', e.target.value),
                        }}
                    />

                    <div className="col-span-2">
                        <OrderItemsTable
                            items={form.data.items}
                            totalAmount={totalAmount}
                            disabled={form.processing}
                            errors={form.errors}
                            onUpdateLine={updateItemLine}
                        />
                    </div>

                    <div className="col-span-2 flex flex-wrap gap-2">
                        <FormSubmitButton
                            loading={form.processing}
                            icon={<Save />}
                            label="Guardar"
                        />
                    </div>
                </form>
            </div>
        </>
    );
}

export default OrderForm;
