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

    public function setData(mixed $key, mixed $value = null, bool $replace = true): static
    {
        // Upon replacing, we simply overwrite the current key's value, with the new value
        // Without replacing, we add the new value to the current value, which becomes an array of values
        if ($replace) {
            $items = is_array($key) ? $key : [$key => $value];
            $data = $this->data;

            foreach ($items as $key => $value) {
                $data[$key] = $value;
            }

            $this->data = $data;

            return $this;
        }

        if (!is_array($key)) {
            $key = [$key => $value];
        }

        $this->data = array_merge_recursive($this->data ?? [], $key);

        return $this;
    }

    public function unsetData(mixed $key): static
    {
        $data = $this->data ?? [];

        unset($data[$key]);

        $this->data = $data;

        return $this;
    }
}