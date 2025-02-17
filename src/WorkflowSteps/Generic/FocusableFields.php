<?php

namespace Startselect\Alfred\WorkflowSteps\Generic;

use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Other\FieldFocus;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

class FocusableFields extends AbstractWorkflowStep
{
    public function register(): Item|array|null
    {
        return [
            (new Item())
                ->name('Focus a field')
                ->info('Focus a HTML input field on this page.')
                ->icon('keyboard')
                ->prefix('field')
                ->trigger(
                    (new ItemSet())
                        ->title('Focus a field')
                        ->placeholder('Search for focusable fields')
                        ->items($this->getFocusableFields())
                )
        ];
    }

    protected function getFocusableFields(): array
    {
        $items = [];

        foreach ($this->pageData->getFocusableFields() as $focusableField) {
            $items[] = (new Item())
                ->name("Field: {$focusableField->getLabel()}")
                ->info('Focus this HTML input field on this page.')
                ->icon('keyboard')
                ->trigger(
                    (new FieldFocus())
                        ->id($focusableField->getId())
                        ->name($focusableField->getName())
                );
        }

        return $items;
    }
}
