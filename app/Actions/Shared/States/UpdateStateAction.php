<?php

namespace App\Actions\Shared\States;

use App\Models\Shared\State;

class UpdateStateAction
{
    /**
     * @param  array{country_id: int, name: string}  $data
     */
    public function execute(State $state, array $data): State
    {
        $state->country_id = $data['country_id'];
        $state->name = $data['name'];
        $state->save();

        return $state->fresh(['country:id,name']) ?? $state;
    }
}
