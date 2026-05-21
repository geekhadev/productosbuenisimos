<?php

namespace App\Enums;

enum ChatbotSource: string
{
    case Web = 'web';
    case Whatsapp = 'whatsapp';
    case Facebook = 'facebook';
    case Instagram = 'instagram';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
