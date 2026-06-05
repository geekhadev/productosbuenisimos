<?php

namespace App\Enums;

enum WhatsappMessageType: string
{
    case Text = 'text';
    case Audio = 'audio';
}
