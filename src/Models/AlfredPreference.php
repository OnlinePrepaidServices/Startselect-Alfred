<?php

namespace Startselect\Alfred\Models;

use Illuminate\Database\Eloquent\Model;
use Startselect\Alfred\Enums\AlfredPreferenceType;

/**
 * @property int $id
 * @property int $owner_id
 * @property AlfredPreferenceType $type
 * @property array $data
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class AlfredPreference extends Model
{
    protected $casts = [
        'type' => AlfredPreferenceType::class,
    ];
}