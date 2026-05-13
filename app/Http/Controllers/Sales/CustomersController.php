<?php

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\Customers\CreateCustomerAction;
use App\Actions\Sales\Customers\DeleteCustomerAction;
use App\Actions\Sales\Customers\ListCustomersAction;
use App\Actions\Sales\Customers\UpdateCustomerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\CustomerListRequest;
use App\Http\Requests\Sales\StoreCustomerRequest;
use App\Http\Requests\Sales\UpdateCustomerRequest;
use App\Models\Sales\Customer;
use App\Models\User;
use App\Support\SelectedCompanySession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomersController extends Controller
{
    public function index(
        CustomerListRequest $request,
        ListCustomersAction $action,
    ): Response {
        $this->authorize('viewAny', Customer::class);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $user = $request->user();
        assert($user instanceof User);

        $paginator = $action->execute($companyId, $request->filtersForAction());

        return Inertia::render('sales/customers/index', [
            'data' => $paginator->through(
                fn (Customer $customer): array => $this->customerRow($user, $customer),
            ),
            'filters' => $request->filtersForFrontend(),
            'can' => [
                'create' => $user->can('create', Customer::class),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Customer::class);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        return Inertia::render('sales/customers/form', [
            'customer' => null,
        ]);
    }

    public function store(
        StoreCustomerRequest $request,
        CreateCustomerAction $action,
    ): RedirectResponse {
        $this->authorize('create', Customer::class);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $action->execute($companyId, $request->customerPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cliente creado.']);

        return to_route('sales.customers.index');
    }

    public function edit(Request $request, Customer $customer): Response
    {
        $this->authorize('update', $customer);

        $customer->load(['addresses']);

        return Inertia::render('sales/customers/form', [
            'customer' => $this->customerFormProps($customer),
        ]);
    }

    public function update(
        UpdateCustomerRequest $request,
        Customer $customer,
        UpdateCustomerAction $action,
    ): RedirectResponse {
        $this->authorize('update', $customer);

        $action->execute($customer, $request->customerPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cliente actualizado.']);

        return to_route('sales.customers.index');
    }

    public function destroy(Request $request, Customer $customer, DeleteCustomerAction $action): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $action->execute($customer);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Cliente dado de baja.']);

        return to_route('sales.customers.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function customerFormProps(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'full_name' => $customer->full_name,
            'phone' => $customer->phone,
            'addresses' => $customer->addresses
                ->map(fn ($address): array => [
                    'id' => $address->id,
                    'country_name' => (string) ($address->country_name ?? ''),
                    'state_name' => (string) ($address->state_name ?? ''),
                    'address' => (string) ($address->address ?? ''),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function customerRow(User $user, Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'full_name' => $customer->full_name,
            'phone' => $customer->phone,
            'created_at' => $customer->created_at?->toIso8601String(),
            'updated_at' => $customer->updated_at?->toIso8601String(),
            'can' => [
                'update' => $user->can('update', $customer),
                'delete' => $user->can('delete', $customer),
            ],
        ];
    }
}
