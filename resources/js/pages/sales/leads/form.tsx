import { Head } from '@inertiajs/react';
import { Save, X } from 'lucide-react';
import { FormLinkButton } from '@/components/custom/form-link-button';
import { FormSelect } from '@/components/custom/form-select';
import { FormSubmitButton } from '@/components/custom/form-submit-button';
import { FormTextInput } from '@/components/custom/form-text-input';
import { useLeadForm } from '@/pages/sales/leads/hooks/use-lead-form';
import {
    LEAD_SOURCE_FORM_OPTIONS,
    LEAD_SOURCE_LABELS,
    LEAD_STATUS_FORM_OPTIONS,
} from '@/pages/sales/leads/labels';
import type { LeadsFormPageProps } from '@/pages/sales/leads/types';
import { index as leadsIndex } from '@/routes/sales/leads';

function LeadForm(props: LeadsFormPageProps) {
    const { form, submit, headTitle, isEdit, customerOptions } = useLeadForm(props);

    const sourceOptions = LEAD_SOURCE_FORM_OPTIONS.map((o) => ({
        id: o.id,
        label: o.label,
    }));
    const statusOptions = LEAD_STATUS_FORM_OPTIONS.map((o) => ({
        id: o.id,
        label: o.label,
    }));

    return (
        <>
            <Head title={headTitle} />

            <div className="flex min-w-0 flex-1 flex-col gap-6 p-4 max-w-3xl">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">{headTitle}</h1>
                        <p className="text-muted-foreground mt-1 text-sm">
                            {isEdit
                                ? 'Actualiza el estado del lead y vincula un cliente si ya realizó una compra.'
                                : 'Registra un contacto manual. El teléfono no tiene que ser único en la empresa.'}
                        </p>
                    </div>
                    <FormLinkButton
                        href={leadsIndex.url()}
                        icon={<X />}
                        label="Volver al listado"
                        buttonVariant="outline"
                        containerClassName="w-auto"
                    />
                </div>

                <form onSubmit={submit} className="space-y-6">
                    {isEdit ? (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <FormTextInput
                                    label="Teléfono"
                                    inputProps={{
                                        id: 'lead-phone',
                                        name: 'phone',
                                        value: form.data.phone,
                                        readOnly: true,
                                        disabled: true,
                                    }}
                                />
                                <FormTextInput
                                    label="Fuente"
                                    inputProps={{
                                        id: 'lead-source-readonly',
                                        name: 'source',
                                        value:
                                            LEAD_SOURCE_LABELS[form.data.source] ??
                                            form.data.source,
                                        readOnly: true,
                                        disabled: true,
                                    }}
                                />
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <FormSelect
                                    label="Estado"
                                    required
                                    error={form.errors.status}
                                    options={statusOptions}
                                    selectProps={{
                                        id: 'lead-status',
                                        name: 'status',
                                        value: form.data.status,
                                        onChange: (e) =>
                                            form.setData('status', e.target.value),
                                    }}
                                />
                                <FormSelect
                                    label="Cliente vinculado"
                                    error={form.errors.customer_id}
                                    options={customerOptions}
                                    placeholder="Sin cliente"
                                    selectProps={{
                                        id: 'lead-customer_id',
                                        name: 'customer_id',
                                        value: form.data.customer_id,
                                        onChange: (e) =>
                                            form.setData('customer_id', e.target.value),
                                    }}
                                />
                            </div>
                        </>
                    ) : (
                        <div className="grid gap-4 sm:grid-cols-2">
                            <FormTextInput
                                label="Teléfono"
                                required
                                error={form.errors.phone}
                                inputProps={{
                                    id: 'lead-phone',
                                    name: 'phone',
                                    maxLength: 40,
                                    value: form.data.phone,
                                    onChange: (e) => form.setData('phone', e.target.value),
                                }}
                            />
                            <FormSelect
                                label="Fuente"
                                required
                                error={form.errors.source}
                                options={sourceOptions}
                                selectProps={{
                                    id: 'lead-source',
                                    name: 'source',
                                    value: form.data.source,
                                    onChange: (e) => form.setData('source', e.target.value),
                                }}
                            />
                        </div>
                    )}

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

export default LeadForm;
