<?php

namespace Startselect\Alfred\Preparations\Core;

use Startselect\Alfred\Preparations\AbstractPreparation;

class Item extends AbstractPreparation
{
    public const KEY_ALT = 'alt';
    public const KEY_CONTROL = 'ctrl';
    public const KEY_SHIFT = 'shift';

    protected ?string $name = null;
    protected string $info = '';
    protected ?string $icon = null;
    protected ?string $prefix = null;
    protected ?array $shortcut = null;
    protected bool $warn = false;
    protected ?AbstractPreparation $trigger = null;

    protected array $validationProperties = [
        'name',
        'trigger',
    ];

    /**
     * The name of the item.
     */
    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * The information / description of the item.
     */
    public function info(string $info): static
    {
        $this->info = $info;

        return $this;
    }

    /**
     * The icon of the item.
     *
     * This is a Font Awesome class after the fa-* part; Just replace the asterisk.
     */
    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Whether the item could be triggered by a prefix while typing in the phrase field.
     */
    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * The keyboard shortcut of the item.
     *
     * Note: Use capital letters.
     */
    public function shortcut(array $shortcut): static
    {
        $this->shortcut = $shortcut;

        return $this;
    }

    /**
     * Warn the user before triggering the item.
     */
    public function showWarning(bool $warn): static
    {
        $this->warn = $warn;

        return $this;
    }

    /**
     * The preparation to execute when the item is triggered.
     */
    public function trigger(AbstractPreparation $preparation): static
    {
        $this->trigger = $preparation;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'name' => $this->name,
            'info' => $this->info,
            'icon' => $this->icon,
            'prefix' => $this->prefix,
            'shortcut' => $this->shortcut,
            'warn' => $this->warn,
            'trigger' => $this->trigger?->toArray(),
        ]);
    }
}
