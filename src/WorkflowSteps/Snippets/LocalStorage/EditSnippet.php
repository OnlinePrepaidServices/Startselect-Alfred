<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets\LocalStorage;

use Startselect\Alfred\Preparations\AbstractPreparation;
use Startselect\Alfred\Preparations\Other\LocalStorage;
use Startselect\Alfred\WorkflowSteps\Snippets\EditSnippet as AbstractEditSnippet;

class EditSnippet extends AbstractEditSnippet
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

    protected function findSnippet(): ?string
    {
        return $this->getSnippets()[$this->getRequiredData('keyword')] ?? null;
    }

    protected function onEdit(array &$snippets): bool
    {
        return true;
    }

    protected function onEditTrigger(array $snippets): AbstractPreparation
    {
        return (new LocalStorage())
            ->key('snippets')
            ->merge(true)
            ->data([
                $this->getRequiredData('keyword') => $this->alfredData->getPhrase(),
            ]);
    }
}
