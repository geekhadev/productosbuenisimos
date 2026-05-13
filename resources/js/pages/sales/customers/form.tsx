import { Head } from '@inertiajs/react';
import { ChevronDown, Plus, Save, Trash2, X } from 'lucide-react';
import { FormLinkButton } from '@/components/custom/form-link-button';
import { FormSubmitButton } from '@/components/custom/form-submit-button';
import { FormTextInput } from '@/components/custom/form-text-input';
import { FormTextarea } from '@/components/custom/form-textarea';
import { Button } from '@/components/ui/button';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { cn } from '@/lib/utils';
import { useCustomerForm } from '@/pages/sales/customers/hooks/use-customer-form';
import type { CustomersFormPageProps } from '@/pages/sales/customers/types';
import { MAX_CUSTOMER_ADDRESSES } from '@/pages/sales/customers/types';
import { index as customersIndex } from '@/routes/sales/customers';

function addressSummary(country: string, state: string, line: string): string {
    const parts = [country.trim(), state.trim(), line.trim()].filter(Boolean);

    if (parts.length === 0) {
        return 'Sin datos';
    }

    const head = parts.slice(0, 2).join(' · ');

    if (parts.length <= 2) {
        return head;
    }

    return `${head} — ${parts[2].slice(0, 48)}${parts[2].length > 48 ? '…' : ''}`;
}

function CustomerForm(props: CustomersFormPageProps) {
    const {
        form,
        submit,
        headTitle,
        addAddressLine,
        removeAddressLine,
        updateAddressLine,
    } = useCustomerForm(props);

    const canAddMore = form.data.addresses.length < MAX_CUSTOMER_ADDRESSES;

    return (
        <>
            <Head title={headTitle} />

            <div className="flex min-w-0 flex-1 flex-col gap-6 p-4 max-w-3xl">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">{headTitle}</h1>
                        <p className="text-muted-foreground mt-1 text-sm">
                            El teléfono debe ser único dentro de la empresa seleccionada.
                        </p>
                    </div>
                    <FormLinkButton
                        href={customersIndex.url()}
                        icon={<X />}
                        label="Volver al listado"
                        buttonVariant="outline"
                        containerClassName="w-auto"
                    />
                </div>

                <form onSubmit={submit} className="space-y-6">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <FormTextInput
                            label="Nombre completo"
                            required
                            error={form.errors.full_name}
                            inputProps={{
                                id: 'customer-full_name',
                                name: 'full_name',
                                maxLength: 255,
                                value: form.data.full_name,
                                onChange: (e) => form.setData('full_name', e.target.value),
                            }}
                        />
                        <FormTextInput
                            label="Teléfono"
                            required
                            error={form.errors.phone}
                            inputProps={{
                                id: 'customer-phone',
                                name: 'phone',
                                maxLength: 40,
                                value: form.data.phone,
                                onChange: (e) => form.setData('phone', e.target.value),
                            }}
                        />
                    </div>

                    <div className="border-border space-y-3 rounded-lg border p-3">
                        <div className="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 className="text-base font-semibold tracking-tight">
                                    Direcciones
                                </h2>
                                <p className="text-muted-foreground text-xs">
                                    Opcional. Solo se guardan filas con al menos un dato. Máximo{' '}
                                    {MAX_CUSTOMER_ADDRESSES}.
                                </p>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                className="shrink-0 gap-1"
                                disabled={!canAddMore || form.processing}
                                onClick={() => {
                                    addAddressLine();
                                }}
                            >
                                <Plus className="size-4" aria-hidden />
                                Añadir
                            </Button>
                        </div>

                        {form.data.addresses.length === 0 ? (
                            <p className="text-muted-foreground py-2 text-center text-sm">
                                No hay direcciones. Pulsa «Añadir» para registrar una.
                            </p>
                        ) : (
                            <div className="space-y-2">
                                {form.data.addresses.map((row, index) => (
                                    <Collapsible
                                        key={row.id ?? `new-${index}`}
                                        defaultOpen={index === 0}
                                        className="border-border rounded-md border bg-card"
                                    >
                                        <div className="flex items-stretch gap-0.5">
                                            <CollapsibleTrigger
                                                type="button"
                                                className={cn(
                                                    'flex min-w-0 flex-1 items-center gap-2 px-3 py-2 text-left text-sm',
                                                    'hover:bg-muted/60 data-[state=open]:bg-muted/40',
                                                    '[&[data-state=open]>svg]:rotate-180',
                                                )}
                                            >
                                                <ChevronDown
                                                    className="text-muted-foreground size-4 shrink-0 transition-transform duration-200"
                                                    aria-hidden
                                                />
                                                <span className="truncate font-medium">
                                                    Dirección {index + 1}
                                                    <span className="text-muted-foreground ml-2 font-normal">
                                                        —{' '}
                                                        {addressSummary(
                                                            row.country_name,
                                                            row.state_name,
                                                            row.address,
                                                        )}
                                                    </span>
                                                </span>
                                            </CollapsibleTrigger>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                className="text-destructive hover:text-destructive shrink-0 rounded-none"
                                                disabled={form.processing}
                                                aria-label={`Eliminar dirección ${index + 1}`}
                                                onClick={() => {
                                                    removeAddressLine(index);
                                                }}
                                            >
                                                <Trash2 className="size-4" />
                                            </Button>
                                        </div>
                                        <CollapsibleContent className="border-border border-t px-3 pb-3 pt-2">
                                            <div className="grid gap-3 sm:grid-cols-2">
                                                <FormTextInput
                                                    label="País"
                                                    containerClassName="gap-1.5"
                                                    labelClassName="text-xs"
                                                    error={
                                                        form.errors[
                                                            `addresses.${index}.country_name`
                                                        ]
                                                    }
                                                    inputProps={{
                                                        id: `customer-address-${index}-country`,
                                                        name: `addresses[${index}][country_name]`,
                                                        maxLength: 120,
                                                        className: 'h-9',
                                                        value: row.country_name,
                                                        onChange: (e) =>
                                                            updateAddressLine(index, {
                                                                country_name: e.target.value,
                                                            }),
                                                    }}
                                                />
                                                <FormTextInput
                                                    label="Estado o región"
                                                    containerClassName="gap-1.5"
                                                    labelClassName="text-xs"
                                                    error={
                                                        form.errors[
                                                            `addresses.${index}.state_name`
                                                        ]
                                                    }
                                                    inputProps={{
                                                        id: `customer-address-${index}-state`,
                                                        name: `addresses[${index}][state_name]`,
                                                        maxLength: 120,
                                                        className: 'h-9',
                                                        value: row.state_name,
                                                        onChange: (e) =>
                                                            updateAddressLine(index, {
                                                                state_name: e.target.value,
                                                            }),
                                                    }}
                                                />
                                            </div>
                                            <div className="mt-2">
                                                <FormTextarea
                                                    label="Calle y detalle"
                                                    placeholder="Calle, número, comuna…"
                                                    containerClassName="gap-1.5"
                                                    labelClassName="text-xs"
                                                    error={
                                                        form.errors[`addresses.${index}.address`]
                                                    }
                                                    textareaProps={{
                                                        id: `customer-address-${index}-line`,
                                                        name: `addresses[${index}][address]`,
                                                        maxLength: 2000,
                                                        rows: 2,
                                                        className: 'min-h-[4rem] resize-y',
                                                        value: row.address,
                                                        onChange: (e) =>
                                                            updateAddressLine(index, {
                                                                address: e.target.value,
                                                            }),
                                                    }}
                                                />
                                            </div>
                                        </CollapsibleContent>
                                    </Collapsible>
                                ))}
                            </div>
                        )}
                    </div>

                    <div className="flex flex-wrap gap-2">
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

export default CustomerForm;
