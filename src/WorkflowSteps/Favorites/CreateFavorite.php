<?php

namespace Startselect\Alfred\WorkflowSteps\Favorites;

use Startselect\Alfred\Preparations\Core\Action;
use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\Preparations\Other\LocalStorage;
use Startselect\Alfred\Preparations\Other\WorkflowStep;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

class CreateFavorite extends AbstractWorkflowStep
{
    public function register(ItemSet $itemSet): void
    {
        $itemSet->addItem(
            (new Item())
                ->name('Create favorite')
                ->info('A new favorite based on the current URL.')
                ->icon('star')
                ->shortcut([
                    Item::KEY_CONTROL,
                    'B',
                ])
                ->trigger(
                    (new WorkflowStep())
                        ->class(static::class)
                        ->method(static::METHOD_INIT)
                )
        );
    }

    public function init(): Response
    {
        return $this->getResponse()
            ->title('Name your new favorite')
            ->placeholder('E.g.: List of my products')
            ->trigger(
                (new Action())
                    ->trigger(
                        (new WorkflowStep())
                            ->class(static::class)
                            ->data($this->getOptionalData())
                    )
            );
    }

    public function handle(): Response
    {
        if (!$this->alfredData->getPhrase()) {
            return $this->failure('Please name your favorite.');
        }

        return $this->getResponse()
            ->notification("Favorite with name `{$this->alfredData->getPhrase()}` was added!")
            ->trigger(
                (new LocalStorage())
                    ->key('favorites')
                    ->merge(true)
                    ->data([
                        $this->alfredData->getPhrase() => $this->pageData->getUrl()->getFullUrl(true),
                    ])
            );
    }
}
