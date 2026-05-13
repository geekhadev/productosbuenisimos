<?php

namespace App\Enums;

enum UserType: string
{
    case Root = 'root';
    case Owner = 'owner';
    case User = 'user';
}
