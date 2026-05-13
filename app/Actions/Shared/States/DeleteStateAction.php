<?php

namespace App\Actions\Shared\States;

use App\Models\Shared\State;

class DeleteStateAction
{
    public function execute(State $state): void
    {
        $state->delete();
    }
}
