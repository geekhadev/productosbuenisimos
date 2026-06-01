import { ShieldAlert } from 'lucide-react';
import { FormPasswordInputGroup } from '@/components/custom/form-password-input-group';
import { FormSubmitButton } from '@/components/custom/form-submit-button';
import { FormTextInput } from '@/components/custom/form-text-input';
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
import type { useProviderCredentialModal } from '@/pages/configuration/fulfillment-providers/hooks/use-provider-credential-modal';

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
                        {provider?.credentialsConfigured ? 'Modificar' : 'Agregar'}{' '}
                        credencial
                        {provider ? ` · ${provider.label}` : ''}
                    </DialogTitle>
                    <DialogDescription>
                        Las credenciales se almacenan cifradas. Solo se mostrarán
                        los últimos caracteres de la contraseña y la fecha de
                        actualización en el listado.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
                    <Alert>
                        <ShieldAlert aria-hidden />
                        <AlertTitle>Seguridad de la credencial</AlertTitle>
                        <AlertDescription className="space-y-2">
                            <p>
                                No compartas estas credenciales con nadie: ni
                                compañeros, ni soporte externo, ni por correo o
                                mensajería.
                            </p>
                            <p>
                                Quien tenga acceso puede operar en tu cuenta del
                                proveedor de fulfillment.
                            </p>
                            <p>
                                Usa credenciales dedicadas a esta aplicación y
                                revócalas de inmediato si sospechas que se
                                filtraron.
                            </p>
                        </AlertDescription>
                    </Alert>

                    <FormTextInput
                        label="URL API"
                        required
                        error={form.errors.api_url}
                        placeholder="https://api.ejemplo.com"
                        inputProps={{
                            id: 'fulfillment-provider-api-url',
                            name: 'api_url',
                            type: 'url',
                            value: form.data.api_url,
                            disabled: form.processing,
                            autoComplete: 'off',
                            onChange: (e) =>
                                form.setData('api_url', e.target.value),
                        }}
                    />

                    <FormTextInput
                        label="Usuario"
                        required
                        error={form.errors.user}
                        placeholder="Usuario del proveedor"
                        inputProps={{
                            id: 'fulfillment-provider-user',
                            name: 'user',
                            value: form.data.user,
                            disabled: form.processing,
                            autoComplete: 'off',
                            onChange: (e) => form.setData('user', e.target.value),
                        }}
                    />

                    <FormPasswordInputGroup
                        label="Contraseña"
                        required={!provider?.credentialsConfigured}
                        error={form.errors.pass}
                        placeholder={
                            provider?.credentialsConfigured
                                ? 'Dejar vacío para mantener la actual'
                                : 'Contraseña del proveedor'
                        }
                        inputProps={{
                            id: 'fulfillment-provider-pass',
                            name: 'pass',
                            value: form.data.pass,
                            disabled: form.processing,
                            autoComplete: 'new-password',
                            onChange: (e) => form.setData('pass', e.target.value),
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
