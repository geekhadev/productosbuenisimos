import { Save, X } from 'lucide-react';
import { FormSubmitButton } from '@/components/custom/form-submit-button';
import { FormTextInput } from '@/components/custom/form-text-input';
import { InertiaFormDialog } from '@/components/custom/inertia-form-dialog';
import { Button } from '@/components/ui/button';
import { DialogFooter } from '@/components/ui/dialog';
import { useSystemForm } from '@/pages/administration/systems/hooks/use-form';
import type { System } from '@/pages/administration/systems/types';

type FormDialogProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    system: System | null;
};

export function FormDialog({ open, onOpenChange, system }: FormDialogProps) {
    const { isEdit, formProps, headTitle } = useSystemForm(system);

    const description = isEdit
        ? 'Actualiza el nombre y el slug del sistema.'
        : 'Define el nombre y el slug (el slug lo escribes tú; no se genera solo).';

    return (
        <InertiaFormDialog<Pick<System, 'name' | 'slug'>>
            open={open}
            onOpenChange={onOpenChange}
            title={headTitle}
            description={description}
            formKey={system?.id ?? 'create'}
            inertiaForm={{ ...formProps }}
        >
            {({ processing, errors }) => (
                <>
                    <FormTextInput
                        label="Nombre"
                        placeholder="Ej. Administración"
                        required
                        error={errors.name}
                        inputProps={{
                            id: 'name',
                            name: 'name',
                            maxLength: 255,
                            defaultValue: system?.name ?? '',
                        }}
                    />

                    <FormTextInput
                        label="Slug"
                        placeholder="Ej. administration"
                        required
                        error={errors.slug}
                        inputProps={{
                            id: 'slug',
                            name: 'slug',
                            maxLength: 255,
                            autoComplete: 'off',
                            defaultValue: system?.slug ?? '',
                        }}
                    />

                    <DialogFooter className="gap-2 sm:justify-end">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => onOpenChange(false)}
                        >
                            <X />
                            Cancelar
                        </Button>
                        <FormSubmitButton
                            type="submit"
                            loading={processing}
                            icon={<Save />}
                            label={isEdit ? 'Guardar cambios' : 'Guardar'}
                            labelLoading="Guardando…"
                            containerClassName="w-auto"
                        />
                    </DialogFooter>
                </>
            )}
        </InertiaFormDialog>
    );
}
