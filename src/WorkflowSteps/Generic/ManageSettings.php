<?php

namespace Startselect\Alfred\WorkflowSteps\Generic;

use Illuminate\Support\Facades\Config;
use Startselect\Alfred\Preparations\Core\Action;
use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\Preparations\ItemTypes\StatusItem;
use Startselect\Alfred\Preparations\Other\WorkflowStep;
use Startselect\Alfred\Preparations\PreparationFactory;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

class ManageSettings extends AbstractWorkflowStep
{
    protected const METHOD_TOGGLE_VALUE = 'toggleValue';
    protected const MANAGEABLE_SETTINGS = [
        'rememberPopularItems' => [
            'name' => 'Remember popular items',
            'info' => 'Keep track of which items you use most and show them when opening Alfred.',
            'type' => 'boolean',
        ],
        'maxPopularItemsOnInit' => [
            'name' => 'Max popular items on init',
            'info' => 'The amount of popular items to show when opening Alfred.',
            'type' => 'integer',
        ],
    ];

    protected array $requiredData = [
        self::METHOD_INIT => [
            'key' => 'Missing setting key.',
            'name' => 'Missing setting name.',
            'value' => 'Missing setting value.',
        ],
        self::METHOD_HANDLE => [
            'key' => 'Missing setting key.',
        ],
        self::METHOD_TOGGLE_VALUE => [
            'key' => 'Missing setting key.',
            'value' => 'Missing setting value.',
        ],
    ];

    public function register(ItemSet $itemSet): void
    {
        $itemSet->addItem(
            (new Item())
                ->name('Manage settings')
                ->info('Change an Alfred setting.')
                ->icon('cog')
                ->prefix('setting')
                ->trigger($this->getSettings())
        );
    }

    public function init(): Response
    {
        // Did we get the necessary data?
        if (!$this->isRequiredDataPresent(self::METHOD_INIT)) {
            return $this->failure();
        }

        return $this->getResponse()
            ->title("Change setting: {$this->getRequiredData('name')}")
            ->placeholder($this->getRequiredData('value'))
            ->trigger(
                (new Action())
                    ->trigger(
                        (new WorkflowStep())
                            ->class(self::class)
                            ->data($this->getRequiredData())
                    )
            );
    }

    public function handle(): Response
    {
        // Did we get the necessary data?
        if (!$this->isRequiredDataPresent(self::METHOD_HANDLE)) {
            return $this->failure();
        }

        // Did we get a phrase?
        if (!$this->alfredData->getPhrase()) {
            return $this->failure('Please set a value.');
        }

        // Save the setting
        $preference = $this->alfredPreferenceManager->settings();
        $preference->setData($this->getRequiredData('key'), $this->alfredData->getPhrase());
        if (!$this->alfredPreferenceManager->save($preference)) {
            return $this->failure('Could not save the setting to the database.');
        }

        return $this->getResponse()
            ->notification('Alfred settings saved successfully.')
            ->trigger(PreparationFactory::reloadState(2));
    }

    public function toggleValue(): Response
    {
        // Did we get the necessary data?
        if (!$this->isRequiredDataPresent(self::METHOD_TOGGLE_VALUE)) {
            return $this->failure();
        }

        // Save the setting
        $preference = $this->alfredPreferenceManager->settings();
        $preference->setData($this->getRequiredData('key'), (bool)$this->getRequiredData('value'));
        if (!$this->alfredPreferenceManager->save($preference)) {
            return $this->failure('Could not save the setting to the database.');
        }

        return $this->getResponse()
            ->notification('Alfred settings saved successfully.')
            ->trigger(PreparationFactory::reloadState(1));
    }

    protected function getSettings(): ItemSet
    {
        $itemSet = (new ItemSet())
            ->title('Change an Alfred setting')
            ->placeholder('Filter by settings..');

        $settings = $this->alfredPreferenceManager->settings()->data;
        $defaultSettings = Config::get('alfred.settings');

        foreach (self::MANAGEABLE_SETTINGS as $key => $manageableSetting) {
            match ($manageableSetting['type']) {
                'boolean' => $itemSet->addItem(
                    (new StatusItem())
                        ->name($manageableSetting['name'])
                        ->when($settings[$key] ?? $defaultSettings[$key], function (StatusItem $item) use ($key) {
                            $item
                                ->status('Yes')
                                ->color('#29b17c');
                        }, function (StatusItem $item) use ($key) {
                            $item
                                ->status('No')
                                ->color('#94a4b5');
                        })
                        ->trigger(
                            (new WorkflowStep())
                                ->class(self::class)
                                ->method(self::METHOD_TOGGLE_VALUE)
                                ->data([
                                    'key' => $key,
                                    'value' => !($settings[$key] ?? $defaultSettings[$key]),
                                ])
                        )
                ),
                'integer' => $itemSet->addItem(
                    (new StatusItem())
                        ->name($manageableSetting['name'])
                        ->info($manageableSetting['info'])
                        ->status($settings[$key] ?? $defaultSettings[$key])
                        ->color('#22292f')
                        ->trigger(
                            (new WorkflowStep())
                                ->class(self::class)
                                ->method(self::METHOD_INIT)
                                ->data([
                                    'key' => $key,
                                    'name' => $manageableSetting['name'],
                                    'value' => $settings[$key] ?? $defaultSettings[$key],
                                ])
                        )
                ),
                default => $this->failure('Unsupported setting detected: ' . $manageableSetting['name']),
            };
        }

        return $itemSet;
    }
}
