<?php

namespace Startselect\Alfred\Preparations\Other;

use Startselect\Alfred\Preparations\AbstractPreparation;

class FieldFocus extends AbstractPreparation
{
    protected ?string $id = null;
    protected ?string $name = null;

    protected array $returnableProperties = [
        'id',
        'name',
    ];

    /**
     * The ID of the HTML input.
     */
    public function id(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * The name of the HTML input.
     */
    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
