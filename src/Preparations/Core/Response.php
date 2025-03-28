<?php

namespace Startselect\Alfred\Preparations\Core;

use Startselect\Alfred\Concerns\AlfredState;
use Startselect\Alfred\Preparations\AbstractPreparation;

class Response extends AbstractPreparation
{
    use AlfredState;

    protected bool $success = true;
    protected ?string $message = null;
    protected ?string $notification = null;
    protected ?AbstractPreparation $trigger = null;

    /**
     * Whether the response is executed successfully.
     */
    public function success(bool $success): static
    {
        $this->success = $success;

        return $this;
    }

    /**
     * Set a message that'll be shown to the user.
     */
    public function message(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set a notification that'll be shown to the user.
     */
    public function notification(string $notification): static
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * The preparation trigger when Alfred receives the response.
     */
    public function trigger(AbstractPreparation $preparation): static
    {
        $this->trigger = $preparation;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'success' => $this->success,
            'alfred' => [
                'help' => $this->help,
                'title' => $this->title,
                'footer' => $this->footer,
                'phrase' => $this->phrase,
                'placeholder' => $this->placeholder,
            ],
            'message' => $this->message,
            'notification' => $this->notification,
            'tips' => $this->tips,
            'trigger' => $this->trigger?->toArray(),
        ]);
    }
}
