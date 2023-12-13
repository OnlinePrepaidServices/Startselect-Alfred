<?php

namespace Startselect\Alfred\Concerns;

trait AlfredState
{
    protected ?string $title = null;
    protected string $phrase = '';
    protected string $placeholder = '';

    /**
     * Set Alfred's title.
     */
    public function title(string $title): static
    {
        $this->title = $title;

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
}
