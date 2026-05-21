<?php

namespace App\Support;

use App\Models\Company;

final class ChatbotCompany
{
    public const NAME = 'PRODUCTOS BUENISIMOS SPA';

    public static function findOrFail(): Company
    {
        return Company::query()
            ->where('name', self::NAME)
            ->firstOrFail();
    }
}
