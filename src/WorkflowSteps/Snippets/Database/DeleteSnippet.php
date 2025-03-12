<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets\Database;

use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\Preparations\Other\SnippetSync;
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
                )
        );
    }

    public function init(): Response
    {
        $preference = $this->alfredPreferenceManager->snippets();

        // Do we have any snippets?
        if (!$preference->data) {
            return $this->failure('You do not have any snippets.');
        }

        // Gather items
        $itemSet = new ItemSet();
        foreach ($preference->data as $keyword => $snippet) {
            $itemSet->addItem(
                (new Item())
                    ->name($keyword)
                    ->info($snippet)
                    ->showWarning(true)
                    ->trigger(
                        (new WorkflowStep())
                            ->class(self::class)
                            ->data(['keyword' => $keyword])
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
        $preference = $this->alfredPreferenceManager->snippets();
        $preference->unsetData($this->getRequiredData('keyword'));

        // Save snippets now that we removed it
        if (!$this->alfredPreferenceManager->save($preference)) {
            return $this->failure('Could not delete the snippet from the database.');
        }

        return $this->getResponse()
            ->trigger(
                (new SnippetSync())
                    ->data($preference->data)
                    ->notification("Snippet with keyword `{$this->getRequiredData('keyword')}` was deleted!")
            );
    }
}
