<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets;

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
                        ->class(static::class)
                        ->method(static::METHOD_INIT)
                )
        );
    }

    public function init(): Response
    {
        // Did we get any snippets?
        if (!$this->preferenceManager->snippets()->data) {
            return $this->failure('You do not have any snippets.');
        }

        // Gather items
        $itemSet = new ItemSet();
        foreach ($this->preferenceManager->snippets()->data as $keyword => $snippet) {
            $itemSet->addItem(
                (new Item())
                    ->name($keyword)
                    ->info($snippet)
                    ->showWarning(true)
                    ->trigger(
                        (new WorkflowStep())
                            ->class(static::class)
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
        if (!$this->isRequiredDataPresent(static::METHOD_HANDLE)) {
            return $this->failure();
        }

        // Were we able to delete it?
        $preference = $this->preferenceManager->snippets();
        $preference->unsetData($this->getRequiredData('keyword'));
        if (!$this->preferenceManager->save($preference)) {
            return $this->failure('Could not delete the snippet.');
        }

        return $this->getResponse()
            ->notification("Snippet with keyword `{$this->getRequiredData('keyword')}` was deleted!")
            ->trigger(
                (new SnippetSync())
                    ->data($preference->data)
            );
    }
}
