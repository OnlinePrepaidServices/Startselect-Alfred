<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets\LocalStorage;

use Startselect\Alfred\Preparations\AbstractPreparation;
use Startselect\Alfred\Preparations\Other\LocalStorage;
use Startselect\Alfred\WorkflowSteps\Snippets\CreateSnippet as AbstractCreateSnippet;

class CreateSnippet extends AbstractCreateSnippet
{
    protected function onSave(): bool
    {
        return true;
    }

    protected function onSaveTrigger(): AbstractPreparation
    {
        return (new LocalStorage())
            ->key('snippets')
            ->merge(true)
            ->data([
                $this->getRequiredData('keyword') => $this->alfredData->getPhrase(),
            ]);
    }
}
