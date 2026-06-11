<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreConversationTagRequest;
use App\Http\Requests\Sales\UpdateConversationTagRequest;
use App\Models\Sales\ConversationTag;
use App\Support\SelectedCompanySession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConversationTagsController extends Controller
{
    public function index(Request $request): Response
    {
        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $tags = ConversationTag::forCompany($companyId)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'description', 'color', 'sort_order'])
            ->map(fn (ConversationTag $tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'description' => $tag->description,
                'color' => $tag->color,
                'sort_order' => $tag->sort_order,
            ])
            ->values()
            ->all();

        return Inertia::render('sales/conversation-tags/index', [
            'tags' => $tags,
        ]);
    }

    public function store(StoreConversationTagRequest $request): RedirectResponse
    {
        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        ConversationTag::query()->create([
            'company_id' => $companyId,
            ...$request->tagPayload(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Etiqueta creada.']);

        return to_route('sales.conversation-tags.index');
    }

    public function update(UpdateConversationTagRequest $request, ConversationTag $conversationTag): RedirectResponse
    {
        $conversationTag->update($request->tagPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Etiqueta actualizada.']);

        return to_route('sales.conversation-tags.index');
    }

    public function destroy(Request $request, ConversationTag $conversationTag): RedirectResponse
    {
        $conversationTag->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Etiqueta eliminada.']);

        return to_route('sales.conversation-tags.index');
    }
}
