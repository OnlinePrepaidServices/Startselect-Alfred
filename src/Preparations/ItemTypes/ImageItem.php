<?php

namespace Startselect\Alfred\Preparations\ItemTypes;

use Startselect\Alfred\Preparations\Core\Item;

class ImageItem extends Item
{
    protected ?string $image = null;

    protected array $validationProperties = [
        'name',
        'trigger',
        'image',
    ];

    /**
     * The image of the item.
     */
    public function image(string $url): static
    {
        $this->image = $url;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'image' => $this->image,
        ]);
    }
}
