<?php

namespace App\Http\Controllers\Public;

use App\Actions\Public\IniciarChatbot;
use App\Actions\Public\NuevaConversacionChatbot;
use App\Actions\Public\SendChatbotMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ChatbotMessageRequest;
use App\Http\Requests\Public\IniciarChatbotRequest;
use App\Http\Requests\Public\NuevaConversacionChatbotRequest;
use Illuminate\Http\JsonResponse;

class ChatbotController extends Controller
{
    public function iniciar(IniciarChatbotRequest $request, IniciarChatbot $action): JsonResponse
    {
        return response()->json(
            $action->execute($request->validated('phone'), $request->source()),
        );
    }

    public function nuevaConversacion(
        NuevaConversacionChatbotRequest $request,
        NuevaConversacionChatbot $action,
    ): JsonResponse {
        return response()->json(
            $action->execute($request->validated('conversation_id'), $request->source()),
        );
    }

    public function mensaje(ChatbotMessageRequest $request, SendChatbotMessage $action): JsonResponse
    {
        return response()->json(
            $action->execute(
                $request->validated('conversation_id'),
                $request->source(),
                $request->validated('message'),
                $request->productContext(),
            ),
        );
    }
}
