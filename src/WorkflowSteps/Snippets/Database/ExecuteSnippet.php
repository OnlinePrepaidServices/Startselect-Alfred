<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets\Database;

use Startselect\Alfred\WorkflowSteps\Snippets\ExecuteSnippet as AbstractExecuteSnippetAlias;

class ExecuteSnippet extends AbstractExecuteSnippetAlias
{
    protected function handlesLocalStorage(): bool
    {
        return false;
    }

    protected function hasSnippets(): bool
    {
        if (!$this->alfredPreferenceManager->snippets()->data) {
            return false;
        }

        return true;
    }

    protected function getSnippets(): array
    {
        return $this->alfredPreferenceManager->snippets()->data;
    }

    protected function findSnippet(): ?string
    {
        return $this->getSnippets()[$this->getRequiredData('keyword')] ?? null;
    }
}
