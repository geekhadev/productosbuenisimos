import { ShieldAlert } from 'lucide-react';
import { FormPasswordInputGroup } from '@/components/custom/form-password-input-group';
import { FormSubmitButton } from '@/components/custom/form-submit-button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { useProviderCredentialModal } from '@/pages/configuration/ai-providers/hooks/use-provider-credential-modal';

type ProviderCredentialModalProps = {
    modal: ReturnType<typeof useProviderCredentialModal>;
};

export function ProviderCredentialModal({ modal }: ProviderCredentialModalProps) {
    const { open, provider, form, handleOpenChange, submit } = modal;

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>
                        {provider?.keyConfigured ? 'Modificar' : 'Agregar'} credencial
                        {provider ? ` · ${provider.label}` : ''}
                    </DialogTitle>
                    <DialogDescription>
                        La clave se almacena cifrada. Solo se mostrarán los últimos
                        caracteres y la fecha de actualización en el listado.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
                    <Alert>
                        <ShieldAlert aria-hidden />
                        <AlertTitle>Seguridad de la credencial</AlertTitle>
                        <AlertDescription className="space-y-2">
                            <p>
                                No compartas esta clave con nadie: ni compañeros, ni
                                soporte externo, ni por correo o mensajería.
                            </p>
                            <p>
                                Quien tenga acceso puede consumir tu cuota, ver datos
                                sensibles o desactivar servicios en tu cuenta del
                                proveedor.
                            </p>
                            <p>
                                Usa credenciales dedicadas a esta aplicación y revócalas
                                de inmediato si sospechas que se filtraron.
                            </p>
                        </AlertDescription>
                    </Alert>

                    <FormPasswordInputGroup
                        label="API key"
                        required
                        error={form.errors.key}
                        placeholder="Pega la clave del proveedor"
                        inputProps={{
                            id: 'provider-credential-key',
                            name: 'key',
                            value: form.data.key,
                            disabled: form.processing,
                            autoComplete: 'off',
                            onChange: (e) => form.setData('key', e.target.value),
                        }}
                    />

                    <DialogFooter className="gap-2 sm:gap-0">
                        <Button
                            type="button"
                            variant="outline"
                            disabled={form.processing}
                            onClick={() => handleOpenChange(false)}
                        >
                            Cancelar
                        </Button>
                        <FormSubmitButton
                            type="submit"
                            label="Guardar credencial"
                            labelLoading="Guardando…"
                            loading={form.processing}
                            disabled={form.processing}
                            containerClassName="w-auto"
                        />
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
