<?php

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\Orders\SendOrdersToContraEntregaAction;
use App\Http\Controllers\Controller;
use App\Models\Sales\Order;
use App\Support\SelectedCompanySession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class OrderFulfillmentController extends Controller
{
    public function sendToContraEntrega(
        Request $request,
        SendOrdersToContraEntregaAction $action,
    ): JsonResponse {
        $this->authorize('viewAny', Order::class);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['required', 'string', 'uuid'],
        ]);

        /** @var list<string> $orderIds */
        $orderIds = $request->input('order_ids');

        try {
            $results = $action->execute($companyId, $orderIds);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['results' => $results]);
    }
}
