<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['default_provider'])]
class WhatsappSetting extends Model
{
    use HasUuids;

    protected $table = 'configuration_whatsapp_settings';

    public static function instance(): static
    {
        return static::query()->firstOrCreate([]);
    }
}
