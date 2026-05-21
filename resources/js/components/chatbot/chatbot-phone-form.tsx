import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type ChatbotPhoneFormProps = {
    error: string | null;
    isLoading: boolean;
    onSubmit: (phone: string) => void;
};

export function ChatbotPhoneForm({ error, isLoading, onSubmit }: ChatbotPhoneFormProps) {
    const [phone, setPhone] = useState('');

    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        onSubmit(phone);
    };

    return (
        <form className="flex flex-col gap-4 p-4" onSubmit={handleSubmit}>
            <div className="space-y-1">
                <h2 className="text-lg font-semibold text-foreground">Asistente de ventas</h2>
                <p className="text-sm text-muted-foreground">
                    Ingresa tu teléfono para chatear con nosotros.
                </p>
            </div>
            <div className="space-y-2">
                <Label htmlFor="chatbot-phone">Tu teléfono</Label>
                <Input
                    id="chatbot-phone"
                    type="tel"
                    autoComplete="tel"
                    placeholder="+56 9 1234 5678"
                    value={phone}
                    onChange={(event) => setPhone(event.target.value)}
                    disabled={isLoading}
                    required
                    minLength={7}
                    maxLength={20}
                />
                <p className="text-xs text-muted-foreground">
                    Lo usamos para retomar tu conversación cuando vuelvas.
                </p>
            </div>
            {error ? <p className="text-sm text-destructive">{error}</p> : null}
            <Button disabled={isLoading || phone.trim().length < 7} type="submit">
                {isLoading ? 'Conectando…' : 'Iniciar chat'}
            </Button>
        </form>
    );
}
