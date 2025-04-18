<?php

namespace Startselect\Alfred\WorkflowSteps\Settings;

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
    protected const METHOD_CHANGE_VALUE = 'changeValue';
    protected const METHOD_SAVE_VALUE = 'saveValue';
    protected const METHOD_TOGGLE_VALUE = 'toggleValue';
    protected const MANAGEABLE_SETTINGS = [
        'displayTips' => [
            'name' => 'Display tips',
            'info' => 'Shows a list of tips, when available, above the items in Alfred.',
            'type' => 'boolean',
        ],
        'rememberPopularItems' => [
            'name' => 'Remember popular items',
            'info' => 'Keep track of which items you use most and show them when opening Alfred.',
            'type' => 'boolean',
        ],
        'maxPopularItemsOnInit' => [
            'name' => 'Max popular items on init',
            'info' => 'The amount of popular items to show when opening Alfred.',
            'type' => 'integer',
            'min' => 1,
            'max' => 10,
        ],
    ];

    protected array $requiredData = [
        self::METHOD_CHANGE_VALUE => [
            'key' => 'Missing setting key.',
            'name' => 'Missing setting name.',
            'value' => 'Missing setting value.',
        ],
        self::METHOD_SAVE_VALUE => [
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
                ->icon('gear')
                ->trigger(
                    (new WorkflowStep())
                        ->class(static::class)
                        ->method(static::METHOD_INIT)
                )
        );
    }

    public function init(): Response
    {
        return $this->getResponse()
            ->title('Change an Alfred setting')
            ->placeholder('Filter by settings..')
            ->trigger($this->getSettingsItemSet());
    }

    public function changeValue(): Response
    {
        // Did we get the necessary data?
        if (!$this->isRequiredDataPresent(static::METHOD_CHANGE_VALUE)) {
            return $this->failure();
        }

        return $this->getResponse()
            ->title("Change setting: {$this->getRequiredData('name')}")
            ->phrase($this->getRequiredData('value'))
            ->trigger(
                (new Action())
                    ->trigger(
                        (new WorkflowStep())
                            ->class(static::class)
                            ->method(static::METHOD_SAVE_VALUE)
                            ->data($this->getRequiredData())
                    )
            );
    }

    public function saveValue(): Response
    {
        // Did we get the necessary data?
        if (!$this->isRequiredDataPresent(static::METHOD_SAVE_VALUE)) {
            return $this->failure();
        }

        // Did we get a phrase?
        if (!$this->alfredData->getPhrase()) {
            return $this->failure('Please set a value.');
        }

        // Get the manageable setting
        $manageableSetting = static::MANAGEABLE_SETTINGS[$this->getRequiredData('key')];

        // Update value to setting type
        $value = match ($manageableSetting['type']) {
            'integer' => (int) $this->alfredData->getPhrase(),
            default => $this->alfredData->getPhrase(),
        };

        // Validate based on type
        $validation = match ($manageableSetting['type']) {
            'integer' => is_int($value) && $value >= $manageableSetting['min'] && $value <= $manageableSetting['max']
                ? true
                : "Value should be a number and have a value between {$manageableSetting['min']} and {$manageableSetting['max']}.",
            default => true,
        };

        if ($validation !== true) {
            return $this->failure($validation);
        }

        // Save the setting
        $preference = $this->preferenceManager->settings();
        $preference->setData($this->getRequiredData('key'), $value);
        if (!$this->preferenceManager->save($preference)) {
            return $this->failure('Could not save the setting.');
        }

        return $this->getResponse()
            ->notification('Alfred settings saved successfully.')
            ->trigger(PreparationFactory::reloadState(2));
    }

    public function toggleValue(): Response
    {
        // Did we get the necessary data?
        if (!$this->isRequiredDataPresent(static::METHOD_TOGGLE_VALUE)) {
            return $this->failure();
        }

        // Save the setting
        $preference = $this->preferenceManager->settings();
        $preference->setData($this->getRequiredData('key'), (bool) $this->getRequiredData('value'));
        if (!$this->preferenceManager->save($preference)) {
            return $this->failure('Could not save the setting.');
        }

        return $this->getResponse()
            ->notification('Alfred settings saved successfully.')
            ->trigger(PreparationFactory::reloadState(1));
    }

    protected function getSettingsItemSet(): ItemSet
    {
        $itemSet = new ItemSet();

        $settings = $this->preferenceManager->settings()->data;
        $defaultSettings = Config::get('alfred.settings');

        foreach (static::MANAGEABLE_SETTINGS as $key => $manageableSetting) {
            match ($manageableSetting['type']) {
                'boolean' => $itemSet->addItem(
                    (new StatusItem())
                        ->name($manageableSetting['name'])
                        ->info($manageableSetting['info'])
                        ->when($settings[$key] ?? $defaultSettings[$key], function (StatusItem $item) {
                            $item
                                ->status('Yes')
                                ->color('#29b17c');
                        }, function (StatusItem $item) {
                            $item
                                ->status('No')
                                ->color('#94a4b5');
                        })
                        ->trigger(
                            (new WorkflowStep())
                                ->class(static::class)
                                ->method(static::METHOD_TOGGLE_VALUE)
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
                                ->class(static::class)
                                ->method(static::METHOD_CHANGE_VALUE)
                                ->data([
                                    'key' => $key,
                                    'name' => $manageableSetting['name'],
                                    'value' => $settings[$key] ?? $defaultSettings[$key],
                                ])
                        )
                ),
            };
        }

        return $itemSet;
    }
}
