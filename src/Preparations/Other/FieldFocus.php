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

    /**
     * The name of the HTML input.
     *
     * @param string $name
     *
     * @return $this
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
