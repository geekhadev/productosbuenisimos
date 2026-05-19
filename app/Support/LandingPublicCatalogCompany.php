<?php

namespace App\Support;

use App\Models\Company;

final class LandingPublicCatalogCompany
{
    public const NAME = 'PRODUCTOS BUENISIMOS SPA';

    public static function find(): ?Company
    {
        return Company::query()
            ->where('name', self::NAME)
            ->first();
    }
}
