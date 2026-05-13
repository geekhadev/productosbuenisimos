import type { InertiaFormProps } from '@inertiajs/react';
import { Save, X } from 'lucide-react';
import { FormLinkButton } from '@/components/custom/form-link-button';
import { FormSelect } from '@/components/custom/form-select';
import { FormSubmitButton } from '@/components/custom/form-submit-button';
import { FormTextInput } from '@/components/custom/form-text-input';
import { DOCUMENT_OPTIONS } from '@/pages/configuration/companies/tabs/tab-config';
import type { CompanyFormData } from '@/pages/configuration/companies/types';
import { index as companiesIndex } from '@/routes/configuration/companies';

type GeneralTabPanelProps = {
    form: InertiaFormProps<CompanyFormData>;
    isEdit: boolean;
};

export function GeneralTabPanel({ form, isEdit }: GeneralTabPanelProps) {
    return (
        <>
            <div>
                <h3 className="text-lg font-medium">Información general</h3>
                <p className="text-muted-foreground text-sm">
                    Agrega la información general de la empresa.
                </p>
                {isEdit ? (
                    <p className="text-muted-foreground mt-2 text-sm">
                        El tipo y número de documento no se pueden modificar
                        después de crear la empresa.
                    </p>
                ) : null}
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
                <FormSelect
                    label="Tipo de documento"
                    required={!isEdit}
                    placeholder=""
                    options={[...DOCUMENT_OPTIONS]}
                    error={form.errors.document_type}
                    selectProps={{
                        id: 'document_type',
                        name: 'document_type',
                        value: String(form.data.document_type ?? ''),
                        onChange: (e) =>
                            form.setData('document_type', e.target.value),
                        disabled: isEdit,
                    }}
                />

                <FormTextInput
                    label="Número de documento"
                    required={!isEdit}
                    error={form.errors.document_number}
                    inputProps={{
                        id: 'document_number',
                        name: 'document_number',
                        maxLength: 255,
                        value: String(form.data.document_number ?? ''),
                        onChange: (e) =>
                            form.setData(
                                'document_number',
                                e.target.value,
                            ),
                        disabled: isEdit,
                    }}
                />

                <FormTextInput
                    label="Nombre"
                    required
                    error={form.errors.name}
                    inputProps={{
                        id: 'name',
                        name: 'name',
                        maxLength: 255,
                        value: String(form.data.name ?? ''),
                        onChange: (e) =>
                            form.setData('name', e.target.value),
                    }}
                />

                <FormTextInput
                    label="Alias"
                    error={form.errors.alias}
                    inputProps={{
                        id: 'alias',
                        name: 'alias',
                        maxLength: 255,
                        value: String(form.data.alias ?? ''),
                        onChange: (e) =>
                            form.setData('alias', e.target.value),
                    }}
                />

                <FormTextInput
                    label="Correo electrónico"
                    type="email"
                    error={form.errors.email}
                    inputProps={{
                        id: 'email',
                        name: 'email',
                        maxLength: 255,
                        value: String(form.data.email ?? ''),
                        onChange: (e) =>
                            form.setData('email', e.target.value),
                    }}
                />

                <FormTextInput
                    label="Teléfono"
                    error={form.errors.phone}
                    inputProps={{
                        id: 'phone',
                        name: 'phone',
                        maxLength: 255,
                        value: String(form.data.phone ?? ''),
                        onChange: (e) =>
                            form.setData('phone', e.target.value),
                    }}
                />

                <FormTextInput
                    label="Dirección"
                    error={form.errors.address}
                    inputProps={{
                        id: 'address',
                        name: 'address',
                        maxLength: 255,
                        value: String(form.data.address ?? ''),
                        onChange: (e) =>
                            form.setData('address', e.target.value),
                    }}
                />
            </div>

            <div className="flex flex-row flex-wrap items-center gap-3">
                <FormSubmitButton
                    type="submit"
                    loading={form.processing}
                    icon={<Save />}
                    label={isEdit ? 'Guardar cambios' : 'Guardar'}
                    labelLoading="Guardando…"
                    containerClassName="w-auto"
                />
                <FormLinkButton
                    href={companiesIndex.url()}
                    buttonVariant="outline"
                    icon={<X />}
                    label="Cancelar"
                    containerClassName="w-auto"
                />
            </div>
        </>
    );
}
