<?php

namespace App\Policies\Configuration;

use App\Enums\UserType;
use App\Models\Configuration\AiProviderCredential;
use App\Models\User;
use App\Support\SelectedCompanySession;

class AiProvidersPolicy
{
    public function view(User $user): bool
    {
        return $this->granted($user, 'configuration.ai-providers.list');
    }

    public function update(User $user, ?AiProviderCredential $credential = null): bool
    {
        return $this->granted($user, 'configuration.ai-providers.update');
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
}
