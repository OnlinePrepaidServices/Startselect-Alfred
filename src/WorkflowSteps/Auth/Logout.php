<?php

namespace Startselect\Alfred\WorkflowSteps\Auth;

use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Other\Redirect;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

class Logout extends AbstractWorkflowStep
{
    public function register(ItemSet $itemSet): void
    {
        $itemSet->addItem(
            (new Item())
                ->name('Logout')
                ->info('End current session.')
                ->icon('power-off')
                ->trigger(
                    (new Redirect())
                        ->url(route('logout'))
                        ->type(Redirect::TYPE_AJAX)
                )
        );
    }
}
