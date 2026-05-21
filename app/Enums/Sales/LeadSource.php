<?php

namespace App\Enums\Sales;

enum LeadSource: string
{
    case Web = 'web';
    case Whatsapp = 'whatsapp';
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case Manual = 'manual';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
