<?php

namespace Startselect\Alfred\WorkflowSteps\Settings\LocalStorage;

use Startselect\Alfred\Preparations\AbstractPreparation;
use Startselect\Alfred\Preparations\Other\LocalStorage;
use Startselect\Alfred\WorkflowSteps\Settings\ManageSettings as AbstractManageSettings;

class ManageSettings extends AbstractManageSettings
{
    protected function handlesLocalStorage(): bool
    {
        return true;
    }

    protected function getSettings(): array
    {
        return $this->alfredData->getWorkflowStep()->getLocalStorageData('settings');
    }

    protected function onSave(mixed $value): bool
    {
        return true;
    }

    protected function onSaveTrigger(mixed $value): AbstractPreparation
    {
        return (new LocalStorage())
            ->key('settings')
            ->merge(true)
            ->data([
                $this->getRequiredData('key') => $value,
            ]);
    }

    protected function onToggleTrigger(): AbstractPreparation
    {
        return (new LocalStorage())
            ->key('settings')
            ->merge(true)
            ->data([
                $this->getRequiredData('key') => (bool) $this->getRequiredData('value'),
            ]);
    }
}
