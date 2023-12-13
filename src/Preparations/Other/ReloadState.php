<?php

namespace Startselect\Alfred\Preparations\Other;

use Startselect\Alfred\Preparations\AbstractPreparation;

class ReloadState extends AbstractPreparation
{
    public const GO_BACK_ONE_STEP = 1;
    public const GO_BACK_TWO_STEPS = 2;

    protected int $steps = 1;

    protected array $returnableProperties = [
        'steps',
    ];

    /**
     * The amount of steps to go back to, before reloading the state.
     */
    public function steps(int $steps): self
    {
        $this->steps = $steps;

        return $this;
    }
}
