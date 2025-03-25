<?php

namespace Startselect\Alfred\Preparations\Other;

use Startselect\Alfred\Preparations\AbstractPreparation;

class SnippetSync extends AbstractPreparation
{
    protected array $data = [];

    protected array $returnableProperties = [
        'data',
    ];

    /**
     * Alfred snippet data.
     */
    public function data(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
