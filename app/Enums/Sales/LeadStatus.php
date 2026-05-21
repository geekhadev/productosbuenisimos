<?php

namespace App\Enums\Sales;

enum LeadStatus: string
{
    case Nuevo = 'nuevo';
    case Contactado = 'contactado';
    case Convertido = 'convertido';
    case Inactivo = 'inactivo';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
