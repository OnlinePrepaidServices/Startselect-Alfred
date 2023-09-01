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
     *
     * @param string $text
     *
     * @return $this
     */
    public function text(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * The notification that is displayed once the clipboard has been altered.
     *
     * @param string $notification
     *
     * @return $this
     */
    public function notification(string $notification): self
    {
        $this->notification = $notification;

        return $this;
    }
}
