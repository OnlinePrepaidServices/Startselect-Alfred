<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets;

use Startselect\Alfred\Preparations\AbstractPreparation;
use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\Preparations\Other\WorkflowStep;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

abstract class DeleteSnippet extends AbstractWorkflowStep
{
    protected array $requiredData = [
        self::METHOD_HANDLE => [
            'keyword' => 'Missing snippet keyword.',
        ],
    ];

    abstract protected function handlesLocalStorage(): bool;
    abstract protected function hasSnippets(): bool;
    abstract protected function getSnippets(): array;
    abstract protected function onDelete(array &$snippets): bool;
    abstract protected function onDeleteTrigger(array $snippets): AbstractPreparation;

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
                        ->when($this->handlesLocalStorage(), function (WorkflowStep $workflowStep) {
                            $workflowStep->includeLocalStorageKeys(['snippets']);
                        })
                )
        );
    }

    public function init(): Response
    {
        // Did we get any snippets?
        if (!$this->hasSnippets()) {
            return $this->failure('You do not have any snippets.');
        }

        // Gather items
        $itemSet = new ItemSet();
        foreach ($this->getSnippets() as $keyword => $snippet) {
            $itemSet->addItem(
                (new Item())
                    ->name($keyword)
                    ->info($snippet)
                    ->showWarning(true)
                    ->trigger(
                        (new WorkflowStep())
                            ->class(static::class)
                            ->data(['keyword' => $keyword])
                            ->when($this->handlesLocalStorage(), function (WorkflowStep $workflowStep) {
                                $workflowStep->includeLocalStorageKeys(['snippets']);
                            })
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

        // Keep track of the snippets
        $snippets = $this->getSnippets();

        // Were we able to save it?
        if (!$this->onDelete($snippets)) {
            return $this->failure('Could not delete the snippet.');
        }

        return $this->getResponse()
            ->notification("Snippet with keyword `{$this->getRequiredData('keyword')}` was deleted!")
            ->trigger($this->onDeleteTrigger($snippets));
    }
}
