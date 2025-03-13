<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets\LocalStorage;

use Startselect\Alfred\WorkflowSteps\Snippets\ExecuteSnippet as AbstractExecuteSnippetAlias;

class ExecuteSnippet extends AbstractExecuteSnippetAlias
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
}
