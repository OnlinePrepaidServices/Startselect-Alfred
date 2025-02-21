<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets;

use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\Preparations\Other\LocalStorage;
use Startselect\Alfred\Preparations\Other\WorkflowStep;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

class DeleteSnippet extends AbstractWorkflowStep
{
    protected array $requiredData = [
        self::METHOD_HANDLE => [
            'keyword' => 'Missing snippet keyword.',
        ],
    ];

    public function register(ItemSet $itemSet): void
    {
        $itemSet->addItem(
            (new Item())
                ->name('Delete snippet')
                ->info('Delete one of your snippets.')
                ->icon('i-cursor')
                ->trigger(
                    (new WorkflowStep())
                        ->class(self::class)
                        ->method(self::METHOD_INIT)
                        ->includeLocalStorageKeys(['snippets'])
                )
        );
    }

    public function init(): Response
    {
        // Did we get any snippets?
        if (!$snippets = $this->alfredData->getWorkflowStep()->getLocalStorageData('snippets')) {
            return $this->failure('You do not have any snippets.');
        }

        // Gather items
        $itemSet = new ItemSet();
        foreach ($snippets as $keyword => $snippet) {
            $itemSet->addItem(
                (new Item())
                    ->name($keyword)
                    ->info($snippet)
                    ->showWarning(true)
                    ->trigger(
                        (new WorkflowStep())
                            ->class(self::class)
                            ->data(['keyword' => $keyword])
                            ->includeLocalStorageKeys(['snippets'])
                    )
            );
        }

        return $this->getResponse()
            ->title('Delete snippet')
            ->placeholder('Filter by your snippets..')
            ->trigger($itemSet);
    }

    public function handle(): Response
    {
        // Did we get the necessary data?
        if (!$this->isRequiredDataPresent(self::METHOD_HANDLE)) {
            return $this->failure();
        }

        // Remove snippet from available snippets
        $snippets = $this->alfredData->getWorkflowStep()->getLocalStorageData('snippets');
        unset($snippets[$this->getRequiredData('keyword')]);

        return $this->getResponse()
            ->trigger(
                (new LocalStorage())
                    ->key('snippets')
                    ->data($snippets)
                    ->notification("Snippet with keyword `{$this->getRequiredData('keyword')}` was deleted!")
            );
    }
}
