<?php

namespace Startselect\Alfred\Preparations\Other;

use Startselect\Alfred\Preparations\AbstractPreparation;

class Clipboard extends AbstractPreparation
{
    protected ?string $text = null;
    protected ?string $notification = null;

    protected array $returnableProperties = [
        'text',
        'notification',
    ];

    /**
     * Text for browser's clipboard.
     */
    public function text(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    /**
     * The notification that is displayed once the clipboard has been altered.
     */
    public function notification(string $notification): static
    {
        $this->notification = $notification;

        return $this;
    }
}
