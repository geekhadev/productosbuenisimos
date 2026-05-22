<?php

namespace App\Policies\Sales;

use App\Enums\UserType;
use App\Models\Public\ChatbotConversation;
use App\Models\User;
use App\Support\SelectedCompanySession;

class ConversationsPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->granted($user, 'sales.conversations.list');
    }

    public function view(User $user, ChatbotConversation $conversation): bool
    {
        return $this->granted($user, 'sales.conversations.list')
            && $this->forSessionCompany($conversation);
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

    private function forSessionCompany(ChatbotConversation $conversation): bool
    {
        $companyId = SelectedCompanySession::selectedCompanyId(request());

        return $companyId !== null
            && $companyId !== ''
            && $conversation->company_id === $companyId;
    }
}
