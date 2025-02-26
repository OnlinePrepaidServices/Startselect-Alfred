<?php

namespace Startselect\Alfred\Preparations\ItemTypes;

use Startselect\Alfred\Preparations\Core\Item;

class ImageItem extends Item
{
    protected ?string $url = null;

    protected array $validationProperties = [
        'name',
        'trigger',
        'url',
    ];

    /**
     * The url of the image in the item.
     */
    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'url' => $this->url,
        ]);
    }
}
