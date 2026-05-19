<?php

namespace App\Policies\Sales;

use App\Enums\UserType;
use App\Models\Sales\SalesAgentConfig;
use App\Models\User;
use App\Support\SelectedCompanySession;

class AgentConfigPolicy
{
    public function view(User $user): bool
    {
        return $this->granted($user, 'sales.agent.list');
    }

    public function update(User $user, ?SalesAgentConfig $config = null): bool
    {
        if (! $this->granted($user, 'sales.agent.update')) {
            return false;
        }

        if ($config === null) {
            return true;
        }

        return $this->forSessionCompany($config);
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

    private function forSessionCompany(SalesAgentConfig $config): bool
    {
        $companyId = SelectedCompanySession::selectedCompanyId(request());

        return $companyId !== null && $companyId !== '' && $config->company_id === $companyId;
    }
}
