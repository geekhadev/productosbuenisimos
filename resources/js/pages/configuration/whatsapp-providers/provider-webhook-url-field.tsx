import { Check, Copy } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useClipboard } from '@/hooks/use-clipboard';

type ProviderWebhookUrlFieldProps = {
    webhookUrl: string;
    providerSlug: string;
    providerLabel: string;
};

export function ProviderWebhookUrlField({
    webhookUrl,
    providerSlug,
    providerLabel,
}: ProviderWebhookUrlFieldProps) {
    const [copiedText, copy] = useClipboard();
    const copied = copiedText === webhookUrl;

    return (
        <div className="space-y-2">
            <Label htmlFor={`webhook-url-${providerSlug}`}>URL de callback</Label>
            <div className="flex gap-2">
                <Input
                    id={`webhook-url-${providerSlug}`}
                    readOnly
                    value={webhookUrl}
                    className="font-mono text-xs"
                />
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    aria-label="Copiar URL de callback"
                    onClick={() => copy(webhookUrl)}
                >
                    {copied ? (
                        <Check className="size-4" />
                    ) : (
                        <Copy className="size-4" />
                    )}
                </Button>
            </div>
            <p className="text-muted-foreground text-sm">
                Copia esta URL y pégala en el campo &quot;Webhook URL&quot; de tu
                cuenta de {providerLabel} → WhatsApp → Sandbox/Number.
            </p>
        </div>
    );
}
