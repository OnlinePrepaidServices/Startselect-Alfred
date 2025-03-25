<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets\Database;

use Startselect\Alfred\Preparations\AbstractPreparation;
use Startselect\Alfred\Preparations\Other\SnippetSync;
use Startselect\Alfred\WorkflowSteps\Snippets\EditSnippet as AbstractEditSnippet;

class EditSnippet extends AbstractEditSnippet
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

    protected function onEdit(array &$snippets): bool
    {
        $preference = $this->preferenceManager->snippets();
        $preference->setData($this->getRequiredData('keyword'), $this->alfredData->getPhrase());

        return $this->preferenceManager->save($preference);
    }

    protected function onEditTrigger(array $snippets): AbstractPreparation
    {
        return (new SnippetSync())
            ->data($this->getSnippets());
    }
}
