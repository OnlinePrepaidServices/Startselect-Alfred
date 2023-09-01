<?php

namespace Startselect\Alfred\ValueObjects;

class AlfredData extends AbstractValueObject
{
    public function getPhrase(): string
    {
        return $this->get('phrase') ?? '';
    }

    public function isRealTimeActionActive(): bool
    {
        return $this->get('realtime', false);
    }

    public function getWorkflowStep(): WorkflowStep
    {
        static $workflowStep;

        if (!$workflowStep) {
            $workflowStep = new WorkflowStep($this->get('workflowStep'));
        }

        return $workflowStep;
    }
}
