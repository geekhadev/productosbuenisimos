<?php

namespace App\Actions\Sales\Conversations;

use App\Models\Public\ChatbotConversation;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ExportConversationTxtAction
{
    /**
     * @var array<string, string>
     */
    private const SOURCE_LABELS = [
        'web' => 'Web',
        'whatsapp' => 'WhatsApp',
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
    ];

    /**
     * @var array<string, string>
     */
    private const CONTACT_TYPE_LABELS = [
        'customer' => 'Cliente',
        'lead' => 'Lead',
        'unknown' => 'Sin lead',
    ];

    /**
     * @var array<string, string>
     */
    private const LEAD_STATUS_LABELS = [
        'nuevo' => 'Nuevo',
        'contactado' => 'Contactado',
        'convertido' => 'Convertido',
        'inactivo' => 'Inactivo',
    ];

    public function __construct(
        private readonly GetConversationDetailAction $detailAction,
    ) {}

    /**
     * @return array{content: string, filename: string}
     */
    public function execute(ChatbotConversation $conversation): array
    {
        $detail = $this->detailAction->execute($conversation);

        $lines = [
            'CONVERSACIÓN',
            str_repeat('=', 12),
            'Contacto: '.$detail['contact_name'],
            'Teléfono: '.$detail['phone'],
            'Fuente: '.($this->sourceLabel($detail['source'])),
            'Tipo: '.$this->contactTypeLabel($detail),
            'Exportado: '.$this->formatTimestamp(now()->toIso8601String()),
            '',
            'MENSAJES',
            str_repeat('=', 8),
            '',
        ];

        if ($detail['messages'] === []) {
            $lines[] = '(Sin mensajes)';
        } else {
            foreach ($detail['messages'] as $message) {
                $lines[] = $this->formatMessage($message, $detail['source']);
                $lines[] = '';
            }
        }

        $content = "\xEF\xBB\xBF".implode("\n", $lines);

        return [
            'content' => $content,
            'filename' => $this->filename($detail),
        ];
    }

    /**
     * @param  array<string, mixed>  $detail
     */
    private function filename(array $detail): string
    {
        $base = Str::slug($detail['contact_name'] ?: $detail['phone'] ?: 'conversacion');
        $date = now()->format('Y-m-d');

        return "conversacion-{$base}-{$date}.txt";
    }

    /**
     * @param  array<string, mixed>  $detail
     */
    private function contactTypeLabel(array $detail): string
    {
        $type = $detail['contact_type'] ?? 'unknown';
        $label = self::CONTACT_TYPE_LABELS[$type] ?? $type;

        if ($type === 'lead' && is_string($detail['lead_status'] ?? null)) {
            $status = self::LEAD_STATUS_LABELS[$detail['lead_status']] ?? $detail['lead_status'];

            return "{$label} · {$status}";
        }

        return $label;
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function formatMessage(array $message, string $conversationSource): string
    {
        $timestamp = $this->formatTimestamp($message['created_at'] ?? now()->toIso8601String());
        $roleLabel = ($message['role'] ?? '') === 'user' ? 'Cliente' : 'Asistente';
        $source = $message['source'] ?? $conversationSource;
        $sourceSuffix = $source !== $conversationSource
            ? ' ('.$this->sourceLabel($source).')'
            : '';

        $lines = [
            "[{$timestamp}] {$roleLabel}{$sourceSuffix}:",
            (string) ($message['content'] ?? ''),
        ];

        foreach ($message['attachments'] ?? [] as $attachment) {
            if (! is_array($attachment)) {
                continue;
            }

            if (($attachment['type'] ?? null) === 'video') {
                $lines[] = 'Video: '.($attachment['product_name'] ?? 'Sin nombre');
                $lines[] = 'URL: '.($attachment['url'] ?? '');
            }
        }

        return implode("\n", $lines);
    }

    private function formatTimestamp(string $iso8601): string
    {
        return Carbon::parse($iso8601)
            ->timezone(config('app.timezone'))
            ->format('d/m/Y H:i');
    }

    private function sourceLabel(string $source): string
    {
        return self::SOURCE_LABELS[$source] ?? $source;
    }
}
