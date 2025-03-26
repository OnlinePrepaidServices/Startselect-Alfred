<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets;

use Startselect\Alfred\Preparations\Core\Action;
use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\Preparations\Other\SnippetSync;
use Startselect\Alfred\Preparations\Other\WorkflowStep;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

class EditSnippet extends AbstractWorkflowStep
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

    public function register(ItemSet $itemSet): void
    {
        $itemSet->addItem(
            (new Item())
                ->name('Edit snippet')
                ->info('Edit one of your snippets.')
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
                    ->trigger(
                        (new WorkflowStep())
                            ->class(static::class)
                            ->data(['keyword' => $keyword])
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
        if (!$this->isRequiredDataPresent(static::METHOD_HANDLE)) {
            return $this->failure();
        }

        // Find snippet to edit
        $snippet = $this->preferenceManager->snippets()->data[$this->getRequiredData('keyword')] ?? null;
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
                            ->class(static::class)
                            ->method(static::METHOD_SAVE)
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
        if (!$this->isRequiredDataPresent(static::METHOD_SAVE)) {
            return $this->failure();
        }

        // Were we able to save it?
        $preference = $this->preferenceManager->snippets();
        $preference->setData($this->getRequiredData('keyword'), $this->alfredData->getPhrase());
        if (!$this->preferenceManager->save($preference)) {
            return $this->failure('Could not update the snippet.');
        }

        return $this->getResponse()
            ->notification("Snippet with keyword `{$this->getRequiredData('keyword')}` was updated!")
            ->trigger(
                (new SnippetSync())
                    ->data($preference->data)
            );
    }
}
