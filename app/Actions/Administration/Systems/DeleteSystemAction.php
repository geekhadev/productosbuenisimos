<?php

namespace App\Actions\Administration\Systems;

use App\Models\Administration\System;

class DeleteSystemAction
{
    public function execute(System $system): void
    {
        $system->delete();
    }
}
