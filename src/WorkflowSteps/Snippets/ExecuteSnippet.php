<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets;

use Startselect\Alfred\Preparations\Core\Action;
use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\Preparations\Other\Clipboard;
use Startselect\Alfred\Preparations\Other\WorkflowStep;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

abstract class ExecuteSnippet extends AbstractWorkflowStep
{
    protected const METHOD_CHANGE_VARIABLES = 'changeVariables';

    protected array $requiredData = [
        self::METHOD_HANDLE => [
            'keyword' => 'Missing snippet keyword.',
        ],
        self::METHOD_CHANGE_VARIABLES => [
            'keyword' => 'Missing snippet keyword.',
            'snippet' => 'Missing snippet.',
            'variable' => 'Missing variable number.',
            'variables' => 'Missing variables.',
        ],
    ];

    abstract protected function handlesLocalStorage(): bool;
    abstract protected function hasSnippets(): bool;
    abstract protected function getSnippets(): array;
    abstract protected function findSnippet(): ?string;

    public function register(ItemSet $itemSet): void
    {
        $itemSet->addItem(
            (new Item())
                ->name('Execute snippet')
                ->info('Execute one of your snippets and add it to the clipboard.')
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
            ->title('Execute snippet')
            ->placeholder('Filter by your snippets..')
            ->trigger($itemSet);
    }

    public function handle(): Response
    {
        // Did we get the necessary data?
        if (!$this->isRequiredDataPresent(self::METHOD_HANDLE)) {
            return $this->failure();
        }

        // Find snippet to execute
        $snippet = $this->findSnippet();
        if (!$snippet) {
            return $this->failure();
        }

        // Do we have any names variables that need changing?
        preg_match_all("/\[[^]]*]/", $snippet, $matches);

        if ($matches[0]) {
            return $this->getResponse()
                ->title("Change variable: {$matches[0][0]}")
                ->trigger(
                    (new Action())
                        ->trigger(
                            (new WorkflowStep())
                                ->class(self::class)
                                ->method(self::METHOD_CHANGE_VARIABLES)
                                ->data([
                                    'keyword' => $this->getRequiredData('keyword'),
                                    'snippet' => $snippet,
                                    'variable' => 1,
                                    'variables' => array_unique($matches[0]),
                                ])
                        )
                );
        }

        return $this->getResponse()
            ->notification("Snippet `{$this->getRequiredData('keyword')}` text available on clipboard!")
            ->trigger(
                (new Clipboard())
                    ->text($snippet)
            );
    }

    public function changeVariables(): Response
    {
        // Did we get a phrase?
        if (!$this->alfredData->getPhrase()) {
            return $this->failure();
        }

        // Did we get the necessary data?
        if (!$this->isRequiredDataPresent(self::METHOD_CHANGE_VARIABLES)) {
            return $this->failure();
        }

        // Find variable to insert
        $variable = $this->getRequiredData('variables')[$this->getRequiredData('variable') - 1] ?? null;
        if (!$variable) {
            return $this->failure();
        }

        $snippet = str_replace($variable, $this->alfredData->getPhrase(), $this->getRequiredData('snippet'));

        // Are we done changing variables?
        if (count($this->getRequiredData('variables')) === $this->getRequiredData('variable')) {
            return $this->getResponse()
                ->notification("Snippet `{$this->getRequiredData('keyword')}` text available on clipboard!")
                ->trigger(
                    (new Clipboard())
                        ->text($snippet)
                );
        }

        return $this->getResponse()
            ->title("Change variable: {$this->getRequiredData('variables')[$this->getRequiredData('variable')]}")
            ->trigger(
                (new Action())
                    ->trigger(
                        (new WorkflowStep())
                            ->class(self::class)
                            ->method(self::METHOD_CHANGE_VARIABLES)
                            ->data(array_merge(
                                $this->getRequiredData(),
                                [
                                    'snippet' => $snippet,
                                    'variable' => $this->getRequiredData('variable') + 1
                                ],
                            ))
                    )
            );
    }
}
