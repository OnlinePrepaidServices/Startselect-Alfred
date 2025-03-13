<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets\Database;

use Startselect\Alfred\Preparations\AbstractPreparation;
use Startselect\Alfred\Preparations\Other\SnippetSync;
use Startselect\Alfred\WorkflowSteps\Snippets\DeleteSnippet as AbstractDeleteSnippet;

class DeleteSnippet extends AbstractDeleteSnippet
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

    protected function onDelete(array &$snippets): bool
    {
        $preference = $this->alfredPreferenceManager->snippets();
        $preference->unsetData($this->getRequiredData('keyword'));

        return $this->alfredPreferenceManager->save($preference);
    }

    protected function onDeleteTrigger(array $snippets): AbstractPreparation
    {
        return (new SnippetSync())
            ->data($this->getSnippets());
    }
}
