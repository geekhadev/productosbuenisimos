<?php

namespace App\Exceptions;

use RuntimeException;

final class DuplicateCompanyOwnerException extends RuntimeException
{
    public static function forCompany(): self
    {
        return new self(__('user.duplicate_company_owner'));
    }
}
