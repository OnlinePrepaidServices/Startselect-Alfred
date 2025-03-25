<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets\Database;

use Startselect\Alfred\Preparations\AbstractPreparation;
use Startselect\Alfred\Preparations\Other\SnippetSync;
use Startselect\Alfred\WorkflowSteps\Snippets\CreateSnippet as AbstractCreateSnippet;

class CreateSnippet extends AbstractCreateSnippet
{
    protected function onSave(): bool
    {
        $preference = $this->preferenceManager->snippets();
        $preference->setData($this->getRequiredData('keyword'), $this->alfredData->getPhrase());

        return $this->preferenceManager->save($preference);
    }

    protected function onSaveTrigger(): AbstractPreparation
    {
        $preference = $this->preferenceManager->snippets();

        return (new SnippetSync())
            ->data($preference->data);
    }
}
