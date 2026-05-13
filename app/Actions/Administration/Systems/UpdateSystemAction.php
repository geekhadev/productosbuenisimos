<?php

namespace App\Actions\Administration\Systems;

use App\Models\Administration\System;

class UpdateSystemAction
{
    /**
     * @param  array{name: string, slug: string}  $data
     */
    public function execute(System $system, array $data): System
    {
        $system->name = $data['name'];
        $system->slug = $data['slug'];
        $system->save();

        return $system->fresh() ?? $system;
    }
}
