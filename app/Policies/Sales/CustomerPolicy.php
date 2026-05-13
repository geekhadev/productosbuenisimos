<?php

namespace App\Policies\Sales;

use App\Enums\UserType;
use App\Models\Sales\Customer;
use App\Models\User;
use App\Support\SelectedCompanySession;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->granted($user, 'sales.customers.list');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->granted($user, 'sales.customers.list') && $this->forSessionCompany($customer);
    }

    public function create(User $user): bool
    {
        return $this->granted($user, 'sales.customers.create');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->granted($user, 'sales.customers.update')
            && $this->forSessionCompany($customer);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $this->granted($user, 'sales.customers.delete')
            && $this->forSessionCompany($customer);
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $user->type === UserType::Root && $this->forSessionCompany($customer);
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->type === UserType::Root && $this->forSessionCompany($customer);
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

    private function forSessionCompany(Customer $customer): bool
    {
        $companyId = SelectedCompanySession::selectedCompanyId(request());

        return $companyId !== null && $companyId !== '' && $customer->company_id === $companyId;
    }
}
