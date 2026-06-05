<?php

namespace App\Actions\Whatsapp;

use Laravel\Ai\Transcription;

class TranscribeWhatsappAudio
{
    public function execute(string $audioContent, string $mimeType): string
    {
        $provider = (string) config('ai.default', 'openai');

        $response = Transcription::fromBase64(
            base64_encode($audioContent),
            $mimeType,
        )->generate($provider);

        return $response->text;
    }
}
