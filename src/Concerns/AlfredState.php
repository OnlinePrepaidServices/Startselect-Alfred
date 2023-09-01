<?php

namespace Startselect\Alfred\Concerns;

trait AlfredState
{
    protected ?string $title = null;
    protected string $phrase = '';
    protected string $placeholder = '';

    /**
     * Set Alfred's title.
     *
     * @param string $title
     *
     * @return static
     */
    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set Alfred's phrase.
     *
     * @param string $phrase
     *
     * @return static
     */
    public function phrase(string $phrase): static
    {
        $this->phrase = $phrase;

        return $this;
    }

    /**
     * Set Alfred's phrase placeholder.
     *
     * @param string $placeholder
     *
     * @return static
     */
    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }
}
