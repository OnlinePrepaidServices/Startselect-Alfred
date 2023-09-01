<?php

namespace Startselect\Alfred\Preparations\Core;

use Startselect\Alfred\Concerns\AlfredState;
use Startselect\Alfred\Preparations\AbstractPreparation;

class Action extends AbstractPreparation
{
    use AlfredState;

    protected bool $extendedPhrase = false;
    protected bool $realtime = false;
    protected ?AbstractPreparation $trigger = null;

    protected array $validationProperties = [
        'trigger',
    ];

    /**
     * Whether the phrase input should be extended (larger / bigger).
     *
     * @param bool $extendedPhrase
     *
     * @return static
     */
    public function extendedPhrase(bool $extendedPhrase): static
    {
        $this->extendedPhrase = $extendedPhrase;

        return $this;
    }

    /**
     * Whether the action should be submitted while typing in the phrase field.
     *
     * @param bool $realtime
     *
     * @return $this
     */
    public function realtime(bool $realtime): self
    {
        $this->realtime = $realtime;

        return $this;
    }

    /**
     * The preparation trigger of this action.
     *
     * @param AbstractPreparation $preparation
     *
     * @return $this
     */
    public function trigger(AbstractPreparation $preparation): self
    {
        $this->trigger = $preparation;

        return $this;
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
                'active' => true,
                'extendedPhrase' => $this->extendedPhrase,
                'realtime' => $this->realtime,
                'trigger' => $this->trigger?->toArray(),
            ],
        ]);
    }
}
