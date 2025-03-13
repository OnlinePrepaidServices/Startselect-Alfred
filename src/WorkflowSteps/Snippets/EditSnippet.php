<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets;

use Startselect\Alfred\Preparations\AbstractPreparation;
use Startselect\Alfred\Preparations\Core\Action;
use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\Preparations\Other\WorkflowStep;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

abstract class EditSnippet extends AbstractWorkflowStep
{
    protected const METHOD_SAVE = 'save';

    protected array $requiredData = [
        self::METHOD_HANDLE => [
            'keyword' => 'Missing snippet keyword.',
        ],
        self::METHOD_SAVE => [
            'keyword' => 'Missing snippet keyword.',
        ],
    ];

    abstract protected function handlesLocalStorage(): bool;
    abstract protected function hasSnippets(): bool;
    abstract protected function getSnippets(): array;
    abstract protected function findSnippet(): ?string;
    abstract protected function onEdit(array &$snippets): bool;
    abstract protected function onEditTrigger(array $snippets): AbstractPreparation;

    public function register(ItemSet $itemSet): void
    {
        $itemSet->addItem(
            (new Item())
                ->name('Edit snippet')
                ->info('Edit one of your snippets.')
                ->icon('i-cursor')
                ->trigger(
                    (new WorkflowStep())
                        ->class(self::class)
                        ->method(self::METHOD_INIT)
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
                    ->trigger(
                        (new WorkflowStep())
                            ->class(self::class)
                            ->data(['keyword' => $keyword])
                            ->when($this->handlesLocalStorage(), function (WorkflowStep $workflowStep) {
                                $workflowStep->includeLocalStorageKeys(['snippets']);
                            })
                    )
            );
        }

        return $this->getResponse()
            ->title('Edit snippet')
            ->placeholder('Filter by your snippets..')
            ->trigger($itemSet);
    }

    public function handle(): Response
    {
        // Did we get the necessary data?
        if (!$this->isRequiredDataPresent(self::METHOD_HANDLE)) {
            return $this->failure();
        }

        // Find snippet to edit
        $snippet = $this->findSnippet();
        if (!$snippet) {
            return $this->failure();
        }

        return $this->getResponse()
            ->title(
                title: "Edit snippet: {$this->getRequiredData('keyword')}",
                help: CreateSnippet::HELP,
            )
            ->phrase($snippet)
            ->footer('Fill out your snippet text and use the confirm button to save.')
            ->trigger(
                (new Action())
                    ->extendedPhrase(true)
                    ->trigger(
                        (new WorkflowStep())
                            ->class(self::class)
                            ->method(self::METHOD_SAVE)
                            ->data([
                                'keyword' => $this->getRequiredData('keyword'),
                            ])
                    )
            );
    }

    public function save(): Response
    {
        // Did we get a phrase?
        if (!$this->alfredData->getPhrase()) {
            return $this->failure();
        }

        // Did we get the necessary data?
        if (!$this->isRequiredDataPresent(self::METHOD_SAVE)) {
            return $this->failure();
        }

        // Keep track of the snippets
        $snippets = $this->getSnippets();

        // Were we able to save it?
        if (!$this->onEdit($snippets)) {
            return $this->failure('Could not update the snippet.');
        }

        return $this->getResponse()
            ->trigger($this->onEditTrigger($snippets));
    }
}
