<?php

namespace App\Enums;

enum ChatbotMessageRole: string
{
    case User = 'user';
    case Assistant = 'assistant';
}
