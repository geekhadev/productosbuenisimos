<?php

namespace App\Policies\Configuration;

use App\Enums\UserType;
use App\Models\Configuration\WhatsappProviderCredential;
use App\Models\User;
use App\Support\SelectedCompanySession;

class WhatsappProvidersPolicy
{
    public function view(User $user): bool
    {
        return $this->granted($user, 'configuration.whatsapp-providers.list');
    }

    public function update(User $user, ?WhatsappProviderCredential $credential = null): bool
    {
        return $this->granted($user, 'configuration.whatsapp-providers.update');
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
