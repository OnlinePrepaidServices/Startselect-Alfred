<?php

namespace Startselect\Alfred\ValueObjects;

use Illuminate\Support\Arr;

abstract class AbstractValueObject
{
    public function __construct(
        protected array $data
    ) {
        //
    }

    public function all(): array
    {
        return $this->data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->data, $key, $default);
    }
}
