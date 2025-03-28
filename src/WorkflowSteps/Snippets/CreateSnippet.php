<?php

namespace Startselect\Alfred\WorkflowSteps\Snippets;

use Startselect\Alfred\Preparations\Core\Action;
use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\Preparations\Other\SnippetSync;
use Startselect\Alfred\Preparations\Other\WorkflowStep;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

class CreateSnippet extends AbstractWorkflowStep
{
    public const HELP =
        'You are able to use placeholders, which can be replaced automatically when you use the
        <strong>Execute snippet</strong> workflow.
        <br><br>
        Create a placeholder using <strong>[]</strong>.
        <br><br>
        <strong>E.g.</strong>: [name]<br>
        <strong>E.g. snippet</strong>: This is a test by [name].
        <br><br>
        When you execute the snippet, you will be asked to give a value to the placeholders you created.
        Once filled out, the complete text will be available on your clipboard.';

    protected array $requiredData = [
        self::METHOD_HANDLE => [
            'keyword' => 'Missing keyword.',
        ],
    ];

    public function register(ItemSet $itemSet): void
    {
        $itemSet->addItem(
            (new Item())
                ->name('Create snippet')
                ->info('Text replacement based on a keyword.')
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
        if ($this->getOptionalData('step') === 'text') {
            return $this->getTextChoice();
        }

        // Start with choosing a keyword
        return $this->getKeywordChoice();
    }

    public function handle(): Response
    {
        // Did we get a phrase?
        if (!$this->alfredData->getPhrase()) {
            return $this->failure('Please set snippet text.');
        }

        // Did we get the necessary data?
        if (!$this->isRequiredDataPresent(static::METHOD_HANDLE)) {
            return $this->failure();
        }

        // Were we able to save it?
        $preference = $this->preferenceManager->snippets();
        $preference->setData($this->getRequiredData('keyword'), $this->alfredData->getPhrase());
        if (!$this->preferenceManager->save($preference)) {
            return $this->failure('Could not save the snippet.');
        }

        return $this->getResponse()
            ->notification("Snippet with keyword `{$this->getRequiredData('keyword')}` was added!")
            ->trigger(
                (new SnippetSync())
                    ->data($preference->data)
            );
    }

    private function getKeywordChoice(): Response
    {
        return $this->getResponse()
            ->title('Snippet keyword')
            ->placeholder('!keyword')
            ->trigger(
                (new Action())
                    ->trigger(
                        (new WorkflowStep())
                            ->class(static::class)
                            ->method(static::METHOD_INIT)
                            ->data([
                                'step' => 'text',
                            ])
                    )
            );
    }

    private function getTextChoice(): Response
    {
        // Did we get a phrase?
        if (!$this->alfredData->getPhrase()) {
            return $this->failure('Please set a keyword.');
        }

        return $this->getResponse()
            ->title(
                title: 'Snippet text',
                help: CreateSnippet::HELP,
            )
            ->placeholder(
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis tempus eros, eu ultrices ligula.'
            )
            ->footer('Fill out your snippet text and use the confirm button to save.')
            ->trigger(
                (new Action())
                    ->extendedPhrase(true)
                    ->trigger(
                        (new WorkflowStep())
                            ->class(static::class)
                            ->data(array_merge(
                                $this->getOptionalData(),
                                [
                                    'keyword' => $this->alfredData->getPhrase(),
                                ]
                            ))
                    )
            );
    }
}
