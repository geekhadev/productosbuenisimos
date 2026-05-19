import { Head } from '@inertiajs/react';
import { Save } from 'lucide-react';
import { FormSubmitButton } from '@/components/custom/form-submit-button';
import InputError from '@/components/input-error';
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
        isToolEnabled,
        toggleTool,
        onPromptChange,
    } = useAgentConfigForm(props);

    const canUpdate = props.can.update;
    const onlyOneToolEnabled = form.data.enabled_tools.length === 1;

    return (
        <>
            <Head title="Agente de Ventas" />

            <div className="flex min-h-0 flex-1 flex-col gap-4 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Agente de Ventas</h1>
                    <p className="text-muted-foreground mt-1 text-sm">
                        Instrucciones del agente y herramientas para la empresa seleccionada.
                    </p>
                </div>

                <form
                    onSubmit={submit}
                    className="flex flex-col gap-6 lg:flex-row lg:items-start lg:gap-8 h-[calc(100vh-170px)]"
                >
                    <aside className="w-full shrink-0 self-start border-b pb-6 lg:w-64 lg:pb-0 lg:pr-6 h-full">
                        <p className="text-muted-foreground mb-3 text-xs font-medium tracking-wide uppercase">
                            Herramientas
                        </p>

                        <ul className="space-y-2">
                            {props.tools.map((tool) => (
                                <ToolSwitchRow
                                    key={tool.slug}
                                    tool={tool}
                                    enabled={isToolEnabled(tool.slug)}
                                    disabled={
                                        !canUpdate ||
                                        (isToolEnabled(tool.slug) && onlyOneToolEnabled)
                                    }
                                    onCheckedChange={(checked) =>
                                        toggleTool(tool.slug, checked)
                                    }
                                />
                            ))}
                        </ul>

                        {form.errors.enabled_tools ? (
                            <p className="text-destructive mt-1.5 text-xs">
                                {form.errors.enabled_tools}
                            </p>
                        ) : null}

                        <FormSubmitButton
                            icon={<Save />}
                            label="Guardar"
                            disabled={!canUpdate || form.processing}
                            containerClassName="mt-3 w-full"
                        />
                    </aside>

                    <section className="flex min-h-0 min-w-0 flex-1 flex-col gap-3 h-full">

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
                                className={cn(
                                    'flex-1 resize-none font-mono text-sm ',
                                )}
                            />
                            <InputError message={form.errors.prompt} />
                        </div>
                    </section>
                </form>
            </div>
        </>
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
        <li className="flex items-start justify-between gap-3 py-1">
            <ToolText tool={tool} />
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

function ToolText({ tool }: { tool: AgentToolDefinition }) {
    return (
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
    );
}

export default AgentConfigEdit;
