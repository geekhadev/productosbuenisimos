<?php

namespace App\Models\Whatsapp;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['message_id'])]
class WhatsappProcessedMessage extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $table = 'whatsapp_processed_messages';
}
