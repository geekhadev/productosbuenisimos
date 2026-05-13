<?php

namespace App\Actions\Shared\States;

use App\Models\Shared\State;

class CreateStateAction
{
    /**
     * @param  array{country_id: int, name: string}  $data
     */
    public function execute(array $data): State
    {
        return State::query()->create([
            'country_id' => $data['country_id'],
            'name' => $data['name'],
        ]);
    }
}
