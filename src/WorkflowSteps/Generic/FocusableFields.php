<?php

namespace Startselect\Alfred\WorkflowSteps\Generic;

use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Other\FieldFocus;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

class FocusableFields extends AbstractWorkflowStep
{
    public function register(ItemSet $itemSet): void
    {
        $itemSet->addItem(
            (new Item())
                ->name('Focus a field')
                ->info('Focus a HTML input field on this page.')
                ->icon('keyboard')
                ->prefix('field')
                ->trigger($this->getFocusableFields())
        );
    }

    protected function getFocusableFields(): ItemSet
    {
        $itemSet = (new ItemSet())
            ->title('Focus a HTML input field on this page')
            ->placeholder('Filter by fields..');

        foreach ($this->pageData->getFocusableFields() as $focusableField) {
            $itemSet->addItem(
                (new Item())
                    ->name($focusableField->getLabel())
                    ->trigger(
                        (new FieldFocus())
                            ->id($focusableField->getId())
                            ->name($focusableField->getName())
                    )
            );
        }

        return $itemSet;
    }
}
