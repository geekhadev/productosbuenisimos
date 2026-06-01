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
import type { useProviderCredentialModal } from '@/pages/configuration/whatsapp-providers/hooks/use-provider-credential-modal';

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
                        los últimos caracteres de los campos secretos y la fecha
                        de actualización en el listado.
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
                                proveedor de WhatsApp.
                            </p>
                            <p>
                                Usa credenciales dedicadas a esta aplicación y
                                revócalas de inmediato si sospechas que se
                                filtraron.
                            </p>
                        </AlertDescription>
                    </Alert>

                    {provider?.fields.map((field) =>
                        field.type === 'secret' ? (
                            <FormPasswordInputGroup
                                key={field.key}
                                label={field.label}
                                required={!provider.credentialsConfigured}
                                error={form.errors[field.key]}
                                placeholder={
                                    provider.credentialsConfigured
                                        ? 'Dejar vacío para mantener el actual'
                                        : field.placeholder ??
                                          `Ingresa ${field.label.toLowerCase()}`
                                }
                                inputProps={{
                                    id: `whatsapp-provider-${field.key}`,
                                    name: field.key,
                                    value: form.data[field.key] ?? '',
                                    disabled: form.processing,
                                    autoComplete: 'new-password',
                                    onChange: (e) =>
                                        form.setData(field.key, e.target.value),
                                }}
                            />
                        ) : (
                            <FormTextInput
                                key={field.key}
                                label={field.label}
                                required
                                error={form.errors[field.key]}
                                placeholder={
                                    field.placeholder ??
                                    `Ingresa ${field.label.toLowerCase()}`
                                }
                                inputProps={{
                                    id: `whatsapp-provider-${field.key}`,
                                    name: field.key,
                                    value: form.data[field.key] ?? '',
                                    disabled: form.processing,
                                    autoComplete: 'off',
                                    onChange: (e) =>
                                        form.setData(field.key, e.target.value),
                                }}
                            />
                        ),
                    )}

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
