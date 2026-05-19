<?php

namespace App\Policies\Sales;

use App\Enums\UserType;
use App\Models\Sales\Order;
use App\Models\User;
use App\Support\SelectedCompanySession;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->granted($user, 'sales.orders.list');
    }

    public function view(User $user, Order $order): bool
    {
        return $this->granted($user, 'sales.orders.list') && $this->forSessionCompany($order);
    }

    public function create(User $user): bool
    {
        return $this->granted($user, 'sales.orders.create');
    }

    public function update(User $user, Order $order): bool
    {
        return $this->granted($user, 'sales.orders.update')
            && $this->forSessionCompany($order);
    }

    public function delete(User $user, Order $order): bool
    {
        return $this->granted($user, 'sales.orders.delete')
            && $this->forSessionCompany($order);
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

    private function forSessionCompany(Order $order): bool
    {
        $companyId = SelectedCompanySession::selectedCompanyId(request());

        return $companyId !== null && $companyId !== '' && $order->company_id === $companyId;
    }
}
