<?php

namespace App\Policies\Sales;

use App\Enums\UserType;
use App\Models\Sales\Lead;
use App\Models\User;
use App\Support\SelectedCompanySession;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->granted($user, 'sales.leads.list');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $this->granted($user, 'sales.leads.list') && $this->forSessionCompany($lead);
    }

    public function create(User $user): bool
    {
        return $this->granted($user, 'sales.leads.create');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $this->granted($user, 'sales.leads.update')
            && $this->forSessionCompany($lead);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $this->granted($user, 'sales.leads.delete')
            && $this->forSessionCompany($lead);
    }

    public function restore(User $user, Lead $lead): bool
    {
        return $user->type === UserType::Root && $this->forSessionCompany($lead);
    }

    public function forceDelete(User $user, Lead $lead): bool
    {
        return $user->type === UserType::Root && $this->forSessionCompany($lead);
    }

    private function granted(User $user, string $slug): bool
    {
        if ($user->type === UserType::Root) {
            return true;
        }

        $companyId = SelectedCompanySession::selectedCompanyId(request());

        if ($companyId === null || $companyId === '') {
            return false;
        }

        return $user->hasCompanyPermission($slug, $companyId);
    }

    private function forSessionCompany(Lead $lead): bool
    {
        $companyId = SelectedCompanySession::selectedCompanyId(request());

        return $companyId !== null && $companyId !== '' && $lead->company_id === $companyId;
    }
}
