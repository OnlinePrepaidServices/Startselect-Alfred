<?php

namespace Startselect\Alfred\Concerns;

trait AlfredState
{
    protected ?string $help = null;
    protected ?string $title = null;
    protected string $phrase = '';
    protected string $placeholder = '';
    protected array $tips = [];

    /**
     * Set Alfred's title and possible help information.
     */
    public function title(string $title, ?string $help = null): static
    {
        $this->title = $title;
        $this->help = $help;

        return $this;
    }

    /**
     * Set Alfred's phrase.
     */
    public function phrase(string $phrase): static
    {
        $this->phrase = $phrase;

        return $this;
    }

    /**
     * Set Alfred's phrase placeholder.
     */
    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Set Alfred's tips to display.
     */
    public function tips(array $tips): static
    {
        $this->tips = $tips;

        return $this;
    }
}
