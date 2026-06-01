<?php

namespace App\Whatsapp;

readonly class WhatsappProcessingResult
{
    /**
     * @param  list<array{type: string, url: string, product_name?: string}>  $attachments
     */
    public function __construct(
        public string $reply,
        public array $attachments,
    ) {}
}
