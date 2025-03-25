<?php

namespace Startselect\Alfred\Preparations\Other;

use Startselect\Alfred\Preparations\AbstractPreparation;

class Clipboard extends AbstractPreparation
{
    protected ?string $text = null;

    protected array $returnableProperties = [
        'text',
    ];

    /**
     * Text for browser's clipboard.
     */
    public function text(string $text): static
    {
        $this->text = $text;

        return $this;
    }
}
