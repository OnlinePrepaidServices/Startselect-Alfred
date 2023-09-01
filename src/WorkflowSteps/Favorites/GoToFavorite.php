<?php

namespace Startselect\Alfred\WorkflowSteps\Favorites;

use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\Preparations\Other\WorkflowStep;
use Startselect\Alfred\Preparations\PreparationFactory;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

class GoToFavorite extends AbstractWorkflowStep
{
    public function register(): Item|array|null
    {
        return (new Item())
            ->name('Go to favorite')
            ->info('Navigate to one of your favorites.')
            ->icon('star')
            ->trigger(
                (new WorkflowStep())
                    ->class(self::class)
                    ->includeLocalStorageKeys(['favorites'])
            );
    }

    public function handle(): Response
    {
        // Did we get any favorites?
        if (!$favorites = $this->alfredData->getWorkflowStep()->getLocalStorageData('favorites')) {
            return $this->failure('You do not have any favorites.');
        }

        // Gather items
        $itemSet = new ItemSet();
        foreach ($favorites as $name => $favorite) {
            $itemSet->addItem(
                (new Item())
                    ->name($name)
                    ->info($favorite)
                    ->trigger(PreparationFactory::redirect($favorite))
            );
        }

        return $this->getResponse()
            ->title('Go to favorite')
            ->placeholder('Filter by your favorites..')
            ->trigger($itemSet);
    }
}
