<?php

namespace Startselect\Alfred\Preparations\Other;

use Startselect\Alfred\Preparations\AbstractPreparation;

class LocalStorage extends AbstractPreparation
{
    protected ?string $key = null;
    protected array $data = [];
    protected bool $merge = false;
    protected int $ttl = 0;
    protected ?string $notification = null;

    protected array $returnableProperties = [
        'key',
        'data',
        'merge',
        'ttl',
        'notification',
    ];

    /**
     * Key for browser's local storage.
     *
     * @param string $key
     *
     * @return $this
     */
    public function key(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Data for browser's local storage.
     *
     * @param array $data
     *
     * @return $this
     */
    public function data(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Whether to merge existing data for given key in browser's local storage.
     *
     * @param bool $merge
     *
     * @return $this
     */
    public function merge(bool $merge): self
    {
        $this->merge = $merge;

        return $this;
    }

    /**
     * Expiration date for browser's local storage.
     *
     * @param int $ttl
     *
     * @return $this
     */
    public function ttl(int $ttl): self
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * The notification that is displayed once the local storage has been updated.
     *
     * @param string $notification
     *
     * @return $this
     */
    public function notification(string $notification): self
    {
        $this->notification = $notification;

        return $this;
    }
}
