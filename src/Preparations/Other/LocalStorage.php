<?php

namespace Startselect\Alfred\Preparations\Other;

use Startselect\Alfred\Preparations\AbstractPreparation;

class LocalStorage extends AbstractPreparation
{
    protected ?string $key = null;
    protected array $data = [];
    protected bool $merge = false;
    protected int $ttl = 0;

    protected array $returnableProperties = [
        'key',
        'data',
        'merge',
        'ttl',
    ];

    /**
     * Key for browser's local storage.
     */
    public function key(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Data for browser's local storage.
     */
    public function data(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Whether to merge existing data for given key in browser's local storage.
     */
    public function merge(bool $merge): static
    {
        $this->merge = $merge;

        return $this;
    }

    /**
     * Expiration date for browser's local storage.
     */
    public function ttl(int $ttl): static
    {
        $this->ttl = $ttl;

        return $this;
    }
}
