<?php

namespace Startselect\Alfred\Preparations\Other;

use Startselect\Alfred\Preparations\AbstractPreparation;

class FillFieldValue extends AbstractPreparation
{
    protected ?string $id = null;
    protected mixed $value = null;
    protected ?string $notification = null;

    protected array $returnableProperties = [
        'id',
        'value',
        'notification',
    ];

    /**
     * The ID of the HTML input.
     */
    public function id(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * The value of the HTML input.
     */
    public function value(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * The notification that is displayed once the value of the HTML input is filled.
     */
    public function notification(string $notification): self
    {
        $this->notification = $notification;

        return $this;
    }
}
