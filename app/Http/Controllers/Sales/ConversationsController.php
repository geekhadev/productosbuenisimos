<?php

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\Conversations\DeleteConversationAction;
use App\Actions\Sales\Conversations\ExportConversationTxtAction;
use App\Actions\Sales\Conversations\GetConversationDetailAction;
use App\Actions\Sales\Conversations\ListConversationsAction;
use App\Actions\Sales\Conversations\MapConversationListItemAction;
use App\Actions\Sales\Conversations\SendOperatorMessageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\ConversationListRequest;
use App\Models\Public\ChatbotConversation;
use App\Support\SelectedCompanySession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class ConversationsController extends Controller
{
    public function index(
        ConversationListRequest $request,
        ListConversationsAction $listAction,
        MapConversationListItemAction $mapAction,
    ): Response {
        $this->authorize('viewAny', ChatbotConversation::class);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $filters = $request->filtersForAction();
        $paginator = $listAction->execute($companyId, $filters);

        return Inertia::render('sales/conversations/index', [
            'conversations' => $paginator->through(
                fn (ChatbotConversation $conversation): array => $mapAction->execute($conversation),
            ),
            'selected' => null,
        ]);
    }

    public function show(
        ConversationListRequest $request,
        ChatbotConversation $conversation,
        ListConversationsAction $listAction,
        GetConversationDetailAction $detailAction,
        MapConversationListItemAction $mapAction,
    ): Response {
        $this->authorize('view', $conversation);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $filters = $request->filtersForAction();
        $perPage = (int) ($filters['per_page'] ?? 25);

        if (! isset($filters['page'])) {
            $filters['page'] = $listAction->pageForConversation($companyId, $conversation, $perPage);
        }

        $paginator = $listAction->execute($companyId, $filters);

        return Inertia::render('sales/conversations/index', [
            'conversations' => $paginator->through(
                fn (ChatbotConversation $item): array => $mapAction->execute($item),
            ),
            'selected' => $detailAction->execute($conversation),
        ]);
    }

    public function toggleAgent(ChatbotConversation $conversation): RedirectResponse
    {
        $this->authorize('view', $conversation);

        $conversation->update(['agent_paused' => ! $conversation->agent_paused]);

        return back();
    }

    public function operatorMessage(
        Request $request,
        ChatbotConversation $conversation,
        SendOperatorMessageAction $action,
    ): RedirectResponse {
        $this->authorize('view', $conversation);

        $validated = $request->validate([
            'message' => 'required|string|max:4000',
        ]);

        $action->execute($conversation, $validated['message']);

        return back();
    }

    public function destroy(
        ChatbotConversation $conversation,
        DeleteConversationAction $action,
    ): RedirectResponse {
        $this->authorize('delete', $conversation);

        $action->execute($conversation);

        return to_route('sales.conversations.index');
    }

    public function downloadTxt(
        ChatbotConversation $conversation,
        ExportConversationTxtAction $action,
    ): HttpResponse {
        $this->authorize('view', $conversation);

        $export = $action->execute($conversation);

        return response($export['content'], 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$export['filename'].'"',
        ]);
    }
}
