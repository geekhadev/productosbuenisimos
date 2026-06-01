<?php

namespace App\Http\Controllers\Webhooks;

use App\Contracts\WhatsappDriver;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessIncomingWhatsappMessage;
use App\Models\Whatsapp\WhatsappProcessedMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TwilioWhatsappController extends Controller
{
    public function __invoke(Request $request, WhatsappDriver $driver): Response
    {
        Log::info('Twilio WhatsApp webhook received', [
            'message_sid' => $request->input('MessageSid'),
            'from' => $request->input('From'),
            'to' => $request->input('To'),
            'has_body' => $request->has('Body'),
            'message_status' => $request->input('MessageStatus'),
        ]);

        $driver->verifyWebhook($request);

        $message = $driver->parseIncomingMessage($request);

        if ($message === null) {
            Log::info('Twilio WhatsApp webhook ignored (no incoming message payload)', [
                'message_sid' => $request->input('MessageSid'),
                'post_param_keys' => array_keys($request->post()),
            ]);

            return response('', 200);
        }

        if (WhatsappProcessedMessage::query()->where('message_id', $message->messageId)->exists()) {
            Log::info('Twilio WhatsApp webhook ignored (duplicate message)', [
                'message_sid' => $message->messageId,
            ]);

            return response('', 200);
        }

        ProcessIncomingWhatsappMessage::dispatch($message);

        Log::info('Twilio WhatsApp webhook queued for processing', [
            'message_sid' => $message->messageId,
            'phone' => $message->phone,
        ]);

        return response('', 200);
    }
}
