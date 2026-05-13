<?php

namespace App\Actions\Administration\Systems;

use App\Models\Administration\System;

class CreateSystemAction
{
    /**
     * @param  array{name: string, slug: string}  $data
     */
    public function execute(array $data): System
    {
        return System::query()->create([
            'name' => $data['name'],
            'slug' => $data['slug'],
        ]);
    }
}
