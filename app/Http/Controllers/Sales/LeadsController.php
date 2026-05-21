<?php

namespace App\Http\Controllers\Sales;

use App\Actions\Sales\Leads\CreateLeadAction;
use App\Actions\Sales\Leads\DeleteLeadAction;
use App\Actions\Sales\Leads\ListLeadsAction;
use App\Actions\Sales\Leads\UpdateLeadAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\LeadListRequest;
use App\Http\Requests\Sales\StoreLeadRequest;
use App\Http\Requests\Sales\UpdateLeadRequest;
use App\Models\Sales\Customer;
use App\Models\Sales\Lead;
use App\Models\User;
use App\Support\SelectedCompanySession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadsController extends Controller
{
    public function index(
        LeadListRequest $request,
        ListLeadsAction $action,
    ): Response {
        $this->authorize('viewAny', Lead::class);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $user = $request->user();
        assert($user instanceof User);

        $paginator = $action->execute($companyId, $request->filtersForAction());

        return Inertia::render('sales/leads/index', [
            'data' => $paginator->through(
                fn (Lead $lead): array => $this->leadRow($user, $lead),
            ),
            'filters' => $request->filtersForFrontend(),
            'can' => [
                'create' => $user->can('create', Lead::class),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Lead::class);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        return Inertia::render('sales/leads/form', [
            'lead' => null,
            'customers' => [],
        ]);
    }

    public function store(
        StoreLeadRequest $request,
        CreateLeadAction $action,
    ): RedirectResponse {
        $this->authorize('create', Lead::class);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $action->execute($companyId, $request->leadPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Lead creado.']);

        return to_route('sales.leads.index');
    }

    public function edit(Request $request, Lead $lead): Response
    {
        $this->authorize('update', $lead);

        $companyId = SelectedCompanySession::selectedCompanyId($request);

        if ($companyId === null || $companyId === '') {
            abort(404);
        }

        $lead->load('customer');

        $customers = Customer::query()
            ->where('company_id', $companyId)
            ->orderBy('full_name')
            ->get();

        return Inertia::render('sales/leads/form', [
            'lead' => $this->leadFormProps($lead),
            'customers' => $customers->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'label' => $customer->full_name.' · '.$customer->phone,
            ])->values()->all(),
        ]);
    }

    public function update(
        UpdateLeadRequest $request,
        Lead $lead,
        UpdateLeadAction $action,
    ): RedirectResponse {
        $this->authorize('update', $lead);

        $action->execute($lead, $request->leadPayload());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Lead actualizado.']);

        return to_route('sales.leads.index');
    }

    public function destroy(Request $request, Lead $lead, DeleteLeadAction $action): RedirectResponse
    {
        $this->authorize('delete', $lead);

        $action->execute($lead);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Lead dado de baja.']);

        return to_route('sales.leads.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function leadFormProps(Lead $lead): array
    {
        return [
            'id' => $lead->id,
            'phone' => $lead->phone,
            'source' => $lead->source->value,
            'status' => $lead->status->value,
            'customer_id' => $lead->customer_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function leadRow(User $user, Lead $lead): array
    {
        return [
            'id' => $lead->id,
            'phone' => $lead->phone,
            'source' => $lead->source->value,
            'status' => $lead->status->value,
            'customer_name' => $lead->customer?->full_name,
            'created_at' => $lead->created_at?->toIso8601String(),
            'can' => [
                'update' => $user->can('update', $lead),
                'delete' => $user->can('delete', $lead),
            ],
        ];
    }
}
