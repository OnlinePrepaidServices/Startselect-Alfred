<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets\LocalStorage;

use Startselect\Alfred\Preparations\AbstractPreparation;
use Startselect\Alfred\Preparations\Other\LocalStorage;
use Startselect\Alfred\WorkflowSteps\Snippets\DeleteSnippet as AbstractDeleteSnippet;

class DeleteSnippet extends AbstractDeleteSnippet
{
    protected function handlesLocalStorage(): bool
    {
        return true;
    }

    protected function hasSnippets(): bool
    {
        return count($this->alfredData->getWorkflowStep()->getLocalStorageData('snippets'));
    }

    protected function getSnippets(): array
    {
        return $this->alfredData->getWorkflowStep()->getLocalStorageData('snippets');
    }

    protected function onDelete(array &$snippets): bool
    {
        unset($snippets[$this->getRequiredData('keyword')]);

        return true;
    }

    protected function onDeleteTrigger(array $snippets): AbstractPreparation
    {
        return (new LocalStorage())
            ->key('snippets')
            ->data($snippets);
    }
}
