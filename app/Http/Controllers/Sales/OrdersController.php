<?php

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\Orders\DeleteOrderAction;
use App\Actions\Sales\Orders\ListOrdersAction;
use App\Actions\Sales\Orders\StoreOrderAction;
use App\Actions\Sales\Orders\UpdateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\OrderListRequest;
use App\Http\Requests\Sales\StoreOrderRequest;
use App\Http\Requests\Sales\UpdateOrderRequest;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use App\Models\User;
use App\Support\SelectedCompanySession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrdersController extends Controller
{
    public function index(
        OrderListRequest $request,
        ListOrdersAction $action,
    ): Response {
        $this->authorize('viewAny', Order::class);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $user = $request->user();
        assert($user instanceof User);

        $paginator = $action->execute($companyId, $request->filtersForAction());

        return Inertia::render('sales/orders/index', [
            'data' => $paginator->through(
                fn (Order $order): array => $this->orderRow($user, $order),
            ),
            'filters' => $request->filtersForFrontend(),
        ]);
    }

    public function store(
        StoreOrderRequest $request,
        StoreOrderAction $action,
    ): RedirectResponse {
        $this->authorize('create', Order::class);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $action->execute($companyId, $request->orderPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pedido creado.']);

        return to_route('sales.orders.index');
    }

    public function edit(Request $request, Order $order): Response
    {
        $this->authorize('update', $order);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $order->load(['items.product', 'customer', 'address']);

        $customers = Customer::query()
            ->where('company_id', $companyId)
            ->with(['addresses'])
            ->orderBy('full_name')
            ->get();

        return Inertia::render('sales/orders/form', [
            'order' => $this->orderFormProps($order),
            'customers' => $customers->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'phone' => $customer->phone,
                'addresses' => $customer->addresses->map(fn ($address): array => [
                    'id' => $address->id,
                    'label' => $this->addressLabel($address),
                ])->values()->all(),
            ])->values()->all(),
        ]);
    }

    public function update(
        UpdateOrderRequest $request,
        Order $order,
        UpdateOrderAction $action,
    ): RedirectResponse {
        $this->authorize('update', $order);

        $action->execute($order, $request->orderPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pedido actualizado.']);

        return to_route('sales.orders.index');
    }

    public function pdf(Order $order): HttpResponse
    {
        $this->authorize('view', $order);

        $order->load(['items.product', 'customer', 'address', 'company']);

        $logoPath = public_path('assets/logo.png');
        $logoData = file_exists($logoPath)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('pdf.order', [
            'order' => $order,
            'company' => $order->company,
            'logoData' => $logoData,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("pedido-{$order->name}.pdf");
    }

    public function destroy(Request $request, Order $order, DeleteOrderAction $action): RedirectResponse
    {
        $this->authorize('delete', $order);

        $action->execute($order);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pedido eliminado.']);

        return to_route('sales.orders.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function orderRow(User $user, Order $order): array
    {
        return [
            'id' => $order->id,
            'name' => $order->name,
            'customer_name' => $order->customer?->full_name ?? '',
            'address_summary' => $this->addressLabel($order->address),
            'total_amount' => (string) $order->total_amount,
            'items_count' => (int) ($order->items_count ?? $order->items()->count()),
            'created_at' => $order->created_at?->toIso8601String(),
            'can' => [
                'view' => $user->can('view', $order),
                'update' => $user->can('update', $order),
                'delete' => $user->can('delete', $order),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function orderFormProps(Order $order): array
    {
        return [
            'id' => $order->id,
            'name' => $order->name,
            'customer_id' => $order->customer_id,
            'address_id' => $order->address_id,
            'total_amount' => (string) $order->total_amount,
            'items' => $order->items
                ->map(fn ($item): array => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => (string) ($item->product?->name ?? ''),
                    'product_code' => (string) ($item->product?->code ?? ''),
                    'product_sku' => (string) ($item->product?->sku ?? ''),
                    'quantity' => $item->quantity,
                    'unit_price' => (string) $item->unit_price,
                    'line_total' => (string) $item->line_total,
                ])
                ->values()
                ->all(),
        ];
    }

    private function addressLabel(?object $address): string
    {
        if ($address === null) {
            return '';
        }

        $parts = array_filter([
            trim((string) ($address->country_name ?? '')),
            trim((string) ($address->state_name ?? '')),
            trim((string) ($address->address ?? '')),
        ]);

        return implode(' · ', $parts);
    }
}
