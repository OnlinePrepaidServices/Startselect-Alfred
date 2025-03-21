<?php

namespace Startselect\Alfred\Preparations\Other;

use Startselect\Alfred\Preparations\AbstractPreparation;

class SnippetSync extends AbstractPreparation
{
    protected array $data = [];
    protected ?string $notification = null;

    protected array $returnableProperties = [
        'data',
        'notification',
    ];

    /**
     * Alfred preference snippet data.
     */
    public function data(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * The notification that is displayed once the snippets have been synced.
     */
    public function notification(string $notification): static
    {
        $this->notification = $notification;

        return $this;
    }
}
