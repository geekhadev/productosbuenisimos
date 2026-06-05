<?php

namespace App\Whatsapp;

use App\Enums\WhatsappMessageType;

readonly class WhatsappIncomingMessage
{
    public function __construct(
        public string $phone,
        public string $body,
        public string $messageId,
        public string $rawFrom,
        public string $toNumber,
        public WhatsappMessageType $type = WhatsappMessageType::Text,
        public ?string $audioUrl = null,
        public ?string $audioMimeType = null,
    ) {}
}
