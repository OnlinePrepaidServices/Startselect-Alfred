<?php

namespace Startselect\Alfred\WorkflowSteps\Favorites;

use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\Preparations\Other\LocalStorage;
use Startselect\Alfred\Preparations\Other\WorkflowStep;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

class DeleteFavorite extends AbstractWorkflowStep
{
    protected array $requiredData = [
        self::METHOD_HANDLE => [
            'name' => 'Missing favorite name.',
        ],
    ];

    public function register(ItemSet $itemSet): void
    {
        $itemSet->addItem(
            (new Item())
                ->name('Delete favorite')
                ->info('Delete one of your favorites.')
                ->icon('star')
                ->trigger(
                    (new WorkflowStep())
                        ->class(static::class)
                        ->method(static::METHOD_INIT)
                        ->includeLocalStorageKeys(['favorites'])
                )
        );
    }

    public function init(): Response
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
                    ->showWarning(true)
                    ->trigger(
                        (new WorkflowStep())
                            ->class(static::class)
                            ->data(['name' => $name])
                            ->includeLocalStorageKeys(['favorites'])
                    )
            );
        }

        return $this->getResponse()
            ->title('Delete favorite')
            ->placeholder('Filter by your favorites..')
            ->trigger($itemSet);
    }

    public function handle(): Response
    {
        // Did we get the necessary data?
        if (!$this->isRequiredDataPresent(static::METHOD_HANDLE)) {
            return $this->failure();
        }

        // Remove favorite from available favorites
        $favorites = $this->alfredData->getWorkflowStep()->getLocalStorageData('favorites');
        unset($favorites[$this->getRequiredData('name')]);

        return $this->getResponse()
            ->trigger(
                (new LocalStorage())
                    ->key('favorites')
                    ->data($favorites)
                    ->notification("Favorite with name `{$this->getRequiredData('name')}` was deleted!")
            );
    }
}
