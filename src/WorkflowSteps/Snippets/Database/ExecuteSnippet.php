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
        if (!$this->preferenceManager->snippets()->data) {
            return false;
        }

        return true;
    }

    protected function getSnippets(): array
    {
        return $this->preferenceManager->snippets()->data;
    }

    protected function findSnippet(): ?string
    {
        return $this->getSnippets()[$this->getRequiredData('keyword')] ?? null;
    }
}
