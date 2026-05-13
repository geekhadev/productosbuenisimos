import { Save, X } from 'lucide-react';
import { FormSubmitButton } from '@/components/custom/form-submit-button';
import { FormTextInput } from '@/components/custom/form-text-input';
import { InertiaFormDialog } from '@/components/custom/inertia-form-dialog';
import { Button } from '@/components/ui/button';
import { DialogFooter } from '@/components/ui/dialog';
import { useUserForm } from '@/pages/configuration/users/hooks/use-form';
import type {
    UserCreateFormFields,
    UserListRow,
    UserUpdateFormFields,
} from '@/pages/configuration/users/types';

type FormDialogProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    editingUser: UserListRow | null;
};

export function FormDialog({
    open,
    onOpenChange,
    editingUser,
}: FormDialogProps) {
    const {
        formProps,
        formOptions,
        formKey,
        title,
        description,
        isEdit,
        defaults,
    } = useUserForm(editingUser);

    return (
        <InertiaFormDialog<UserCreateFormFields | UserUpdateFormFields>
            open={open}
            onOpenChange={onOpenChange}
            title={title}
            description={description}
            formKey={formKey}
            inertiaForm={{ ...formProps }}
            formOptions={formOptions}
        >
            {({ processing, errors }) => (
                <>
                    <FormTextInput
                        label="Nombre completo"
                        placeholder="Ej. Ana Pérez"
                        required
                        error={errors.name}
                        inputProps={{
                            id: 'user-name',
                            name: 'name',
                            maxLength: 255,
                            autoComplete: 'name',
                            defaultValue: defaults.name,
                        }}
                    />

                    <FormTextInput
                        label="Correo"
                        placeholder="Ej. ana@empresa.cl"
                        type="email"
                        required
                        error={errors.email}
                        inputProps={{
                            id: 'user-email',
                            name: 'email',
                            maxLength: 255,
                            autoComplete: 'off',
                            defaultValue: defaults.email,
                        }}
                    />

                    {!isEdit ? (
                        <>
                            <FormTextInput
                                label="Contraseña"
                                placeholder="Ej. mínimo 8 caracteres"
                                type="password"
                                required
                                error={errors.password}
                                inputProps={{
                                    id: 'user-password',
                                    name: 'password',
                                    autoComplete: 'new-password',
                                    defaultValue: '',
                                }}
                            />

                            <FormTextInput
                                label="Confirmar contraseña"
                                placeholder="Repite la contraseña"
                                type="password"
                                required
                                error={errors.password_confirmation}
                                inputProps={{
                                    id: 'user-password_confirmation',
                                    name: 'password_confirmation',
                                    autoComplete: 'new-password',
                                    defaultValue: '',
                                }}
                            />
                        </>
                    ) : null}

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
                            label="Guardar"
                            labelLoading="Guardando…"
                            containerClassName="w-auto"
                        />
                    </DialogFooter>
                </>
            )}
        </InertiaFormDialog>
    );
}
