<?php

namespace Startselect\Alfred\Preparations\Other;

use Startselect\Alfred\Preparations\AbstractPreparation;

class FieldFocus extends AbstractPreparation
{
    protected ?string $id = null;

    protected array $returnableProperties = [
        'id'
    ];

    /**
     * The ID of the HTML input.
     *
     * @param string $id
     *
     * @return $this
     */
    public function id(string $id): self
    {
        $this->id = $id;

        return $this;
    }
}
