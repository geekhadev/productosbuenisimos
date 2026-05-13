<?php

namespace App\Enums;

enum CompanyDocumentType: string
{
    case ID = 'ID';
    case Rut = 'RUT';
    case Pasaporte = 'Pasaporte';
}
