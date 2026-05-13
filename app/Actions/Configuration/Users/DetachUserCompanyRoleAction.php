<?php

namespace App\Actions\Configuration\Users;

use App\Enums\UserType;
use App\Models\User;
use App\Models\UserCompanyRole;
use App\Support\SelectedCompanySession;
use Illuminate\Http\Request;

class DetachUserCompanyRoleAction
{
    public function execute(
        Request $request,
        User $actor,
        User $target,
        UserCompanyRole $assignment,
    ): void {
        if ((string) $assignment->user_id !== (string) $target->id) {
            abort(404);
        }

        if ($actor->type !== UserType::Root) {
            $companyId = SelectedCompanySession::selectedCompanyId($request);

            if (! is_string($companyId) || $companyId === '' || (string) $assignment->company_id !== $companyId) {
                abort(403);
            }
        }

        $assignment->delete();
    }
}
