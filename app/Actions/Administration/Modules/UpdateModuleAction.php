<?php

namespace App\Actions\Administration\Modules;

use App\Models\Administration\Module;
use App\Models\Administration\System;

class UpdateModuleAction
{
    /**
     * @param  array{name: string, slug: string, system_id: string}  $data  `slug` = segmento (sin prefijo de sistema)
     */
    public function execute(Module $module, array $data): Module
    {
        $system = System::query()->findOrFail($data['system_id']);
        $storedSlug = Module::composeStoredSlug($system, $data['slug']);

        $module->update([
            'name' => $data['name'],
            'slug' => $storedSlug,
            'system_id' => $data['system_id'],
        ]);

        return $module->refresh();
    }
}
