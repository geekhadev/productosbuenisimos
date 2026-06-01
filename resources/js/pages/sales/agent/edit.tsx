import { Head } from '@inertiajs/react';
import { ChevronDown, Save } from 'lucide-react';
import { FormSelect } from '@/components/custom/form-select';
import { FormSubmitButton } from '@/components/custom/form-submit-button';
import InputError from '@/components/input-error';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';
import { useAgentConfigForm } from '@/pages/sales/agent/hooks/use-agent-config-form';
import type { AgentConfigEditPageProps, AgentToolDefinition } from '@/pages/sales/agent/types';

function AgentConfigEdit(props: AgentConfigEditPageProps) {
    const { form, submit, onPromptChange, onProviderChange } =
        useAgentConfigForm(props);

    const canUpdate = props.can.update;

    const providerOptions = props.providers.map((option) => ({
        id: option.value,
        label: option.label,
    }));

    const modelOptions = (props.providerModels[form.data.provider] ?? []).map(
        (option) => ({
            id: option.value,
            label: option.label,
        }),
    );

    return (
        <>
            <Head title="Agente de Ventas" />

            <div className="flex min-h-0 flex-1 flex-col gap-4 p-4">
                <PageHeader />

                <form
                    onSubmit={submit}
                    className="flex h-[calc(100vh-170px)] flex-col gap-6 lg:flex-row lg:items-start lg:gap-8"
                >
                    <aside className="w-full shrink-0 self-start space-y-4 border-b pb-6 lg:w-86 lg:border-r lg:border-b-0 lg:pb-0 lg:pr-6">
                        {props.hasConfiguredProviders ? (
                            <FormSelect
                                label="Proveedor"
                                error={form.errors.provider}
                                options={providerOptions}
                                selectProps={{
                                    id: 'agent-provider',
                                    name: 'provider',
                                    value: form.data.provider,
                                    disabled: !canUpdate,
                                    onChange: (e) =>
                                        onProviderChange(e.target.value),
                                }}
                            />
                        ) : (
                            <p className="text-muted-foreground text-sm">
                                No hay proveedores con credencial configurada. Agrega
                                al menos una en Configuración → Proveedores de IA.
                            </p>
                        )}

                        <FormSelect
                            label="Modelo"
                            error={form.errors.model}
                            options={modelOptions}
                            placeholder=""
                            selectProps={{
                                id: 'agent-model',
                                name: 'model',
                                value: form.data.model,
                                disabled:
                                    !canUpdate ||
                                    !props.hasConfiguredProviders ||
                                    modelOptions.length === 0,
                                onChange: (e) =>
                                    form.setData('model', e.target.value),
                            }}
                        />

                        <Collapsible className="rounded-md border">
                            <CollapsibleTrigger
                                type="button"
                                className={cn(
                                    'flex w-full items-center justify-between gap-2 px-3 py-2.5 text-left text-sm font-medium',
                                    'hover:bg-muted/60 data-[state=open]:bg-muted/40',
                                    '[&[data-state=open]>svg]:rotate-180',
                                )}
                            >
                                <span>Herramientas</span>
                                <ChevronDown
                                    className="text-muted-foreground size-4 shrink-0 transition-transform duration-200"
                                    aria-hidden
                                />
                            </CollapsibleTrigger>
                            <CollapsibleContent className="border-t px-2 pb-2 pt-1">
                                <ul className="space-y-1">
                                    {props.tools.map((tool) => (
                                        <ToolInfoRow key={tool.slug} tool={tool} />
                                    ))}
                                </ul>
                                {form.errors.enabled_tools ? (
                                    <p className="text-destructive mt-1.5 px-1 text-xs">
                                        {form.errors.enabled_tools}
                                    </p>
                                ) : null}
                            </CollapsibleContent>
                        </Collapsible>

                        <FormSubmitButton
                            icon={<Save />}
                            label="Guardar"
                            disabled={
                                !canUpdate ||
                                !props.hasConfiguredProviders ||
                                form.processing
                            }
                            containerClassName="w-full"
                        />
                    </aside>

                    <section className="flex h-full min-h-0 min-w-0 flex-1 flex-col">
                        <div className="flex min-h-0 flex-1 flex-col gap-1.5">
                            <Label htmlFor="agent-prompt" className="sr-only">
                                Instrucciones del agente
                            </Label>
                            <Textarea
                                id="agent-prompt"
                                name="prompt"
                                value={form.data.prompt}
                                readOnly={!canUpdate}
                                onChange={(e) => onPromptChange(e.target.value)}
                                className="flex-1 resize-none font-mono text-sm"
                            />
                            <InputError message={form.errors.prompt} />
                        </div>
                    </section>
                </form>
            </div>
        </>
    );
}

function PageHeader() {
    return (
        <div>
            <h1 className="text-2xl font-semibold tracking-tight">Agente de Ventas</h1>
            <p className="text-muted-foreground mt-1 text-sm">
                Instrucciones del agente y herramientas para la empresa seleccionada.
            </p>
        </div>
    );
}

function ToolInfoRow({ tool }: { tool: AgentToolDefinition }) {
    return (
        <li className="rounded-md px-1 py-1.5">
            <p className="text-sm leading-snug font-medium">{tool.label}</p>
            <p className="text-muted-foreground mt-0.5 text-xs leading-relaxed">
                {tool.description}
            </p>
        </li>
    );
}

export default AgentConfigEdit;
