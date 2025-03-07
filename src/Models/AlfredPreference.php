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
        'data' => 'array',
    ];

    public function setData(mixed $key, mixed $value = null): self
    {
        if (!is_array($key)) {
            $key = [$key => $value];
        }

        $this->data = array_merge_recursive($this->data ?? [], $key);

        return $this;
    }

    public function unsetData(mixed $key): self
    {
        $data = $this->data ?? [];

        unset($data[$key]);

        $this->data = $data;

        return $this;
    }
}