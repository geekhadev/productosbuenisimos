<?php

namespace App\Actions\Administration\Modules;

use App\Models\Administration\Module;

class DeleteModuleAction
{
    public function execute(Module $module): void
    {
        $module->delete();
    }
}
