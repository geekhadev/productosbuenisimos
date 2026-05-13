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
import { usePermissionForm } from '@/pages/administration/permissions/hooks/use-form';
import type {
    ModuleOption,
    Permission,
    SystemOption,
} from '@/pages/administration/permissions/types';

function slugSegmentDefault(permission: Permission | null): string {
    if (permission === null || permission.module === undefined) {
        return '';
    }

    const prefix = `${permission.module.slug}.`;

    if (permission.slug.startsWith(prefix)) {
        return permission.slug.slice(prefix.length);
    }

    return permission.slug;
}

type PermissionFormPayload = {
    module_id: string;
    name: string;
    slug: string;
};

type FormDialogProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    permission: Permission | null;
    systems: SystemOption[];
    modules: ModuleOption[];
};

export function FormDialog({
    open,
    onOpenChange,
    permission,
    systems,
    modules,
}: FormDialogProps) {
    const { isEdit, formProps, headTitle } = usePermissionForm(permission);
    const slugFieldId = useId();
    const slugErrorId = `${slugFieldId}-error`;

    const [systemId, setSystemId] = useState('');
    const [moduleId, setModuleId] = useState('');

    useEffect(() => {
        if (!open) {
            return;
        }

        if (permission?.module?.system_id !== undefined) {
            setSystemId(String(permission.module.system_id));
        } else {
            setSystemId('');
        }

        setModuleId(permission ? String(permission.module_id) : '');
    }, [open, permission]);

    const modulesForSystem = useMemo(
        () =>
            systemId === ''
                ? modules
                : modules.filter((m) => String(m.system_id) === systemId),
        [modules, systemId],
    );

    const selectedModule = useMemo(
        () => modules.find((m) => String(m.id) === moduleId),
        [modules, moduleId],
    );

    const slugPrefix =
        selectedModule !== undefined ? `${selectedModule.slug}.` : '';

    const slugSegmentInitial = useMemo(
        () => slugSegmentDefault(permission),
        [permission],
    );

    const slugMaxLength = useMemo(() => {
        if (selectedModule === undefined) {
            return 191;
        }

        return Math.max(1, 255 - selectedModule.slug.length - 1);
    }, [selectedModule]);

    const systemOptions: FormSelectOption[] = useMemo(
        () =>
            systems.map((s) => ({
                id: String(s.id),
                label: s.name,
            })),
        [systems],
    );

    const moduleOptions: FormSelectOption[] = useMemo(
        () =>
            modulesForSystem.map((m) => ({
                id: String(m.id),
                label: m.name,
            })),
        [modulesForSystem],
    );

    const description = isEdit
        ? 'Actualiza el permiso. El slug guardado combina el módulo elegido y el segmento que escribes a la derecha del prefijo.'
        : 'Elige sistema y módulo, luego el nombre. El slug final será el del módulo más el segmento (p. ej. administration.systems.list).';

    return (
        <InertiaFormDialog<PermissionFormPayload>
            open={open}
            onOpenChange={onOpenChange}
            title={headTitle}
            description={description}
            formKey={permission?.id ?? 'create'}
            inertiaForm={{ ...formProps }}
        >
            {({ processing, errors }) => (
                <>
                    <input type="hidden" name="module_id" value={moduleId} />
                    <FormSelect
                        label="Sistema"
                        placeholder="Selecciona un sistema"
                        required
                        options={systemOptions}
                        selectProps={{
                            id: 'ui_system_id',
                            value: systemId,
                            onChange: (e) => {
                                const next = e.target.value;
                                setSystemId(next);
                                setModuleId('');
                            },
                        }}
                    />

                    <FormSelect
                        label="Módulo"
                        placeholder="Selecciona un módulo"
                        required
                        error={errors.module_id}
                        options={moduleOptions}
                        selectProps={{
                            id: 'module_id_ui',
                            value: moduleId,
                            onChange: (e) => setModuleId(e.target.value),
                            disabled:
                                systemId === '' || moduleOptions.length === 0,
                        }}
                    />

                    <FormTextInput
                        label="Nombre"
                        placeholder="Ej. Listar"
                        required
                        error={errors.name}
                        inputProps={{
                            id: 'name',
                            name: 'name',
                            maxLength: 255,
                            defaultValue: permission?.name ?? '',
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
                                placeholder="Ej. list"
                                defaultValue={slugSegmentInitial}
                                key={`${permission?.id ?? 'new'}-${slugSegmentInitial}-${moduleId}`}
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
