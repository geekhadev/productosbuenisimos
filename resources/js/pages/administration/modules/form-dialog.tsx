import { Save, X } from 'lucide-react';
import { useEffect, useId, useMemo, useState } from 'react';
import type { FormSelectOption } from '@/components/custom/form-select';
import { FormSelect } from '@/components/custom/form-select';
import { FormSubmitButton } from '@/components/custom/form-submit-button';
import { FormTextInput } from '@/components/custom/form-text-input';
import { InertiaFormDialog } from '@/components/custom/inertia-form-dialog';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { DialogFooter } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { useModuleForm } from '@/pages/administration/modules/hooks/use-form';
import type { Module, SystemOption } from '@/pages/administration/modules/types';

function slugSegmentDefault(module: Module | null): string {
    if (module === null || module.system === undefined) {
        return '';
    }

    const prefix = `${module.system.slug}.`;

    if (module.slug.startsWith(prefix)) {
        return module.slug.slice(prefix.length);
    }

    return module.slug;
}

type FormDialogProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    module: Module | null;
    systems: SystemOption[];
};

export function FormDialog({
    open,
    onOpenChange,
    module,
    systems,
}: FormDialogProps) {
    const { isEdit, formProps, headTitle } = useModuleForm(module);
    const slugFieldId = useId();
    const slugErrorId = `${slugFieldId}-error`;

    const [systemId, setSystemId] = useState(() =>
        module ? String(module.system_id) : '',
    );

    useEffect(() => {
        setSystemId(module ? String(module.system_id) : '');
    }, [module]);

    const selectedSystem = useMemo(
        () => systems.find((s) => String(s.id) === systemId),
        [systems, systemId],
    );

    const slugPrefix =
        selectedSystem !== undefined ? `${selectedSystem.slug}.` : '';

    const slugSegmentInitial = useMemo(
        () => slugSegmentDefault(module),
        [module],
    );

    const slugMaxLength = useMemo(() => {
        if (selectedSystem === undefined) {
            return 191;
        }

        return Math.max(1, 255 - selectedSystem.slug.length - 1);
    }, [selectedSystem]);

    const systemOptions: FormSelectOption[] = useMemo(
        () =>
            systems.map((s) => ({
                id: String(s.id),
                label: s.name,
            })),
        [systems],
    );

    const description = isEdit
        ? 'Actualiza el módulo. El slug guardado combina el sistema elegido y el segmento que escribes a la derecha del prefijo.'
        : 'Elige sistema y nombre. El slug final será el prefijo del sistema más el segmento que escribes (p. ej. administration.systems).';

    return (
        <InertiaFormDialog<Pick<Module, 'name' | 'slug' | 'system_id'>>
            open={open}
            onOpenChange={onOpenChange}
            title={headTitle}
            description={description}
            formKey={module?.id ?? 'create'}
            inertiaForm={{ ...formProps }}
        >
            {({ processing, errors }) => (
                <>
                    <FormSelect
                        label="Sistema"
                        placeholder="Selecciona un sistema"
                        required
                        error={errors.system_id}
                        options={systemOptions}
                        selectProps={{
                            id: 'system_id',
                            name: 'system_id',
                            value: systemId,
                            onChange: (e) => setSystemId(e.target.value),
                        }}
                    />

                    <FormTextInput
                        label="Nombre"
                        placeholder="Ej. Permisos"
                        required
                        error={errors.name}
                        inputProps={{
                            id: 'name',
                            name: 'name',
                            maxLength: 255,
                            defaultValue: module?.name ?? '',
                        }}
                    />

                    <div className="grid w-full gap-2">
                        <Label htmlFor={slugFieldId}>
                            Slug
                            <span aria-hidden="true"> (*)</span>
                        </Label>
                        <div
                            className={cn(
                                'flex w-full min-w-0 overflow-hidden rounded-md border border-input bg-transparent shadow-xs transition-[color,box-shadow]',
                                'focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50',
                                errors.slug
                                    ? 'border-destructive ring-destructive/20 dark:ring-destructive/40'
                                    : '',
                            )}
                        >
                            <span
                                className="inline-flex shrink-0 items-center border-r border-input bg-muted px-3 py-2 font-mono text-sm text-muted-foreground"
                                aria-hidden={slugPrefix === ''}
                            >
                                {slugPrefix !== '' ? slugPrefix : '—'}
                            </span>
                            <Input
                                id={slugFieldId}
                                name="slug"
                                type="text"
                                required
                                maxLength={slugMaxLength}
                                autoComplete="off"
                                placeholder="Ej. permissions"
                                defaultValue={slugSegmentInitial}
                                key={`${module?.id ?? 'new'}-${slugSegmentInitial}`}
                                className="rounded-none border-0 shadow-none focus-visible:ring-0 md:text-sm"
                                aria-invalid={errors.slug ? true : undefined}
                                aria-describedby={
                                    errors.slug ? slugErrorId : undefined
                                }
                            />
                        </div>
                        <InputError id={slugErrorId} message={errors.slug} />
                    </div>

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
