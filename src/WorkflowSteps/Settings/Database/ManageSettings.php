<?php

namespace Startselect\Alfred\WorkflowSteps\Settings\Database;

use Startselect\Alfred\Preparations\AbstractPreparation;
use Startselect\Alfred\Preparations\PreparationFactory;
use Startselect\Alfred\WorkflowSteps\Settings\ManageSettings as AbstractManageSettings;

class ManageSettings extends AbstractManageSettings
{
    protected function handlesLocalStorage(): bool
    {
        return false;
    }

    protected function getSettings(): array
    {
        return $this->preferenceManager->settings()->data;
    }

    protected function onSave(mixed $value): bool
    {
        $preference = $this->preferenceManager->settings();
        $preference->setData($this->getRequiredData('key'), $value);

        return $this->preferenceManager->save($preference);
    }

    protected function onSaveTrigger(mixed $value): AbstractPreparation
    {
        return PreparationFactory::reloadState(2);
    }

    protected function onToggleTrigger(): AbstractPreparation
    {
        return PreparationFactory::reloadState(1);
    }
}
