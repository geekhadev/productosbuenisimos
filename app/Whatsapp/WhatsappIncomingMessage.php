<?php

namespace App\Whatsapp;

readonly class WhatsappIncomingMessage
{
    public function __construct(
        public string $phone,
        public string $body,
        public string $messageId,
        public string $rawFrom,
        public string $toNumber,
    ) {}
}
