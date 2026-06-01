<?php

namespace App\Policies\Configuration;

use App\Enums\UserType;
use App\Models\Configuration\FulfillmentProviderCredential;
use App\Models\User;
use App\Support\SelectedCompanySession;

class FulfillmentProvidersPolicy
{
    public function view(User $user): bool
    {
        return $this->granted($user, 'configuration.fulfillment-providers.list');
    }

    public function update(User $user, ?FulfillmentProviderCredential $credential = null): bool
    {
        return $this->granted($user, 'configuration.fulfillment-providers.update');
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
