import { Head } from '@inertiajs/react';
import { ChevronDown, Save } from 'lucide-react';
import { FormSelect } from '@/components/custom/form-select';
import { FormSubmitButton } from '@/components/custom/form-submit-button';
import { FormTextInput } from '@/components/custom/form-text-input';
import InputError from '@/components/input-error';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';
import { useAgentConfigForm } from '@/pages/sales/agent/hooks/use-agent-config-form';
import type { AgentConfigEditPageProps, AgentToolDefinition } from '@/pages/sales/agent/types';

function AgentConfigEdit(props: AgentConfigEditPageProps) {
    const {
        form,
        submit,
        enabledToolsCount,
        isToolEnabled,
        toggleTool,
        onPromptChange,
    } = useAgentConfigForm(props);

    const canUpdate = props.can.update;
    const onlyOneToolEnabled = form.data.enabled_tools.length === 1;

    const providerOptions = props.providers.map((option) => ({
        value: option.value,
        label: option.label,
    }));

    return (
        <>
            <Head title="Agente de Ventas" />

            <div className="flex min-h-0 flex-1 flex-col gap-4 p-4">
                <PageHeader />

                <form
                    onSubmit={submit}
                    className="flex h-[calc(100vh-170px)] flex-col gap-6 lg:flex-row lg:items-start lg:gap-8"
                >
                    <aside className="w-full shrink-0 self-start space-y-4 border-b pb-6 lg:w-64 lg:border-r lg:border-b-0 lg:pb-0 lg:pr-6">
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
                                    form.setData('provider', e.target.value),
                            }}
                        />

                        <FormTextInput
                            label="Modelo"
                            error={form.errors.model}
                            inputProps={{
                                id: 'agent-model',
                                name: 'model',
                                placeholder: 'gpt-4o-mini',
                                value: form.data.model,
                                readOnly: !canUpdate,
                                onChange: (e) =>
                                    form.setData('model', e.target.value),
                            }}
                        />

                        <Collapsible defaultOpen className="rounded-md border">
                            <CollapsibleTrigger
                                type="button"
                                className={cn(
                                    'flex w-full items-center justify-between gap-2 px-3 py-2.5 text-left text-sm font-medium',
                                    'hover:bg-muted/60 data-[state=open]:bg-muted/40',
                                    '[&[data-state=open]>svg]:rotate-180',
                                )}
                            >
                                <span>
                                    Herramientas
                                    <span className="text-muted-foreground ml-1.5 font-normal">
                                        ({enabledToolsCount} activas)
                                    </span>
                                </span>
                                <ChevronDown
                                    className="text-muted-foreground size-4 shrink-0 transition-transform duration-200"
                                    aria-hidden
                                />
                            </CollapsibleTrigger>
                            <CollapsibleContent className="border-t px-2 pb-2 pt-1">
                                <ul className="space-y-1">
                                    {props.tools.map((tool) => (
                                        <ToolSwitchRow
                                            key={tool.slug}
                                            tool={tool}
                                            enabled={isToolEnabled(tool.slug)}
                                            disabled={
                                                !canUpdate ||
                                                (isToolEnabled(tool.slug) &&
                                                    onlyOneToolEnabled)
                                            }
                                            onCheckedChange={(checked) =>
                                                toggleTool(tool.slug, checked)
                                            }
                                        />
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
                            disabled={!canUpdate || form.processing}
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

type ToolSwitchRowProps = {
    tool: AgentToolDefinition;
    enabled: boolean;
    disabled: boolean;
    onCheckedChange: (checked: boolean) => void;
};

function ToolSwitchRow({
    tool,
    enabled,
    disabled,
    onCheckedChange,
}: ToolSwitchRowProps) {
    return (
        <li className="flex items-start justify-between gap-2 rounded-md px-1 py-1.5 hover:bg-muted/50">
            <div className="min-w-0 flex-1">
                <Label
                    htmlFor={`tool-${tool.slug}`}
                    className="cursor-pointer text-sm leading-snug font-medium"
                >
                    {tool.label}
                </Label>
                <p className="text-muted-foreground mt-0.5 text-xs leading-relaxed">
                    {tool.description}
                </p>
            </div>
            <Switch
                id={`tool-${tool.slug}`}
                checked={enabled}
                disabled={disabled}
                onCheckedChange={onCheckedChange}
                className="mt-0.5 shrink-0"
            />
        </li>
    );
}

export default AgentConfigEdit;
