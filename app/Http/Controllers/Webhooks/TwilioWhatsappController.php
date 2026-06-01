<?php

namespace App\Http\Controllers\Webhooks;

use App\Contracts\WhatsappDriver;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessIncomingWhatsappMessage;
use App\Models\Whatsapp\WhatsappProcessedMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TwilioWhatsappController extends Controller
{
    public function __invoke(Request $request, WhatsappDriver $driver): Response
    {
        $driver->verifyWebhook($request);

        $message = $driver->parseIncomingMessage($request);

        if ($message === null) {
            return response('', 200);
        }

        if (WhatsappProcessedMessage::query()->where('message_id', $message->messageId)->exists()) {
            return response('', 200);
        }

        ProcessIncomingWhatsappMessage::dispatch($message);

        return response('', 200);
    }
}
