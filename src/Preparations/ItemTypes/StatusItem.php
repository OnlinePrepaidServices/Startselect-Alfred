<?php

namespace Startselect\Alfred\Preparations\ItemTypes;

use Startselect\Alfred\Preparations\Core\Item;

class StatusItem extends Item
{
    protected ?bool $switched = null;

    protected array $validationProperties = [
        'name',
        'trigger',
        'switched',
    ];

    /**
     * Status switch(ed).
     *
     * @param bool $switched
     *
     * @return $this
     */
    public function switched(bool $switched): self
    {
        $this->switched = $switched;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'switched' => $this->switched,
        ]);
    }
}
