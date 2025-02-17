<?php

namespace Startselect\Alfred\Preparations\Core;

use Startselect\Alfred\Concerns\AlfredState;
use Startselect\Alfred\Preparations\AbstractPreparation;

class ItemSet extends AbstractPreparation
{
    use AlfredState;

    protected ?array $items = null;
    protected bool $sortItems = true;
    protected bool $reverseSorting = false;

    protected array $validationProperties = [
        'items',
    ];

    /**
     * Add an item that'll be used by Alfred.
     */
    public function addItem(Item $item): self
    {
        if (!$this->items) {
            $this->items = [];
        }

        $this->items[] = $item;

        return $this;
    }

    /**
     * Add multiple items that'll be used by Alfred.
     *
     * @param array<Item> $items
     */
    public function addItems(array $items): self
    {
        if (!$this->items) {
            $this->items = [];
        }

        $this->items = array_merge($this->items, $items);

        return $this;
    }

    /**
     * The items that'll be used by Alfred.
     *
     * @param array<Item> $items
     */
    public function items(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Whether Alfred should sort the items.
     */
    public function sort(bool $sortItems, bool $reverseSorting = false): self
    {
        $this->sortItems = $sortItems;
        $this->reverseSorting = $reverseSorting;

        return $this;
    }

    /**
     * Get the items.
     *
     * @return array<Item>|null
     */
    protected function getItems(bool $toArray = false): ?array
    {
        if ($this->items && $toArray) {
            $items = [];
            foreach ($this->items as $item) {
                if ($item instanceof Item && $item->isValid()) {
                    $items[] = $item->toArray();
                }
            }

            // Reverse the sorting?
            if ($this->reverseSorting) {
                $items = array_reverse($items);
            }

            return $items;
        }

        return $this->items;
    }

    /**
     * Get the sorted items.
     *
     * @return array<Item>|null
     */
    protected function getSortedItems(): ?array
    {
        if ($this->getItems()) {
            $items = $this->getItems(true);
            $reverse = $this->reverseSorting;

            // Sort by name
            usort($items, function ($left, $right) use ($reverse) {
                return $reverse
                    ? strnatcmp($right['name'], $left['name'])
                    : strnatcmp($left['name'], $right['name']);
            });

            return $items;
        }

        return null;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'properties' => [
                'alfred' => [
                    'title' => $this->title,
                    'phrase' => $this->phrase,
                    'placeholder' => $this->placeholder,
                ],
                'items' => $this->getItems()
                    ? ($this->sortItems ? $this->getSortedItems() : $this->getItems(true))
                    : null,
                'tips' => $this->tips,
            ],
        ]);
    }
}
