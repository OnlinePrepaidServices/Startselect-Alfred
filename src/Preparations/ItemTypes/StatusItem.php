<?php

namespace Startselect\Alfred\Preparations\ItemTypes;

use Startselect\Alfred\Preparations\Core\Item;

class StatusItem extends Item
{
    protected ?string $status = null;
    protected ?string $color = '#94a4b5';

    protected array $validationProperties = [
        'name',
        'trigger',
        'status',
        'color',
    ];

    /**
     * The status of the item.
     */
    public function status(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * The status's color to be displayed in the item.
     */
    public function color(string $hexColor): self
    {
        $this->color = $hexColor;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'status' => $this->status,
            'color' => $this->color,
        ]);
    }
}
