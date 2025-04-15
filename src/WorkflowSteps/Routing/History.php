<?php

namespace Startselect\Alfred\WorkflowSteps\Routing;

use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\Preparations\Other\Redirect;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

class History extends AbstractWorkflowStep
{
    protected const MAX_HISTORY_RESULTS = 50;

    public function register(ItemSet $itemSet): void
    {
        $itemSet->addItem(
            (new Item())
                ->name('History')
                ->info('Navigate to a page you have recently visited.')
                ->icon('clock-rotate-left')
                ->shortcut([
                    Item::KEY_CONTROL,
                    'H',
                ])
                ->trigger($this->getHistory())
        );
    }

    public function handle(): Response
    {
        return $this->getResponse()
            ->title('History')
            ->placeholder('Filter by navigated pages..')
            ->trigger($this->getHistory());
    }

    protected function getHistory(): ItemSet
    {
        $itemSet = (new ItemSet())
            ->title('History')
            ->placeholder('Filter by navigated pages..')
            ->sort(false, true);

        // Do we have history items?
        $currentHistory = Session::get('alfred.history');
        if (!$currentHistory) {
            return $itemSet;
        }

        foreach ($currentHistory as $historyItem) {
            $itemSet->addItem(
                (new Item())
                    ->name($historyItem['name'])
                    ->info($historyItem['info'] ?? '')
                    ->trigger(
                        (new Redirect())->url(url($historyItem['url']))
                    )
            );
        }

        return $itemSet;
    }

    public static function addPageToHistory(?string $pageTitle = null, ?string $defaultTitle = null): void
    {
        // Get current history
        $currentHistory = Session::get('alfred.history');

        // Get page title by URL?
        if (!$pageTitle || $pageTitle === $defaultTitle) {
            // Base the title on the URL path
            $titleParts = [];
            foreach (explode('/', Request::path()) as $urlPart) {
                if ($urlPart) {
                    $titleParts[] = ucfirst(str_replace('-', ' ', $urlPart));
                }
            }

            $pageTitle = implode(' / ', $titleParts);
        }

        // Does the history already have this action?
        if (isset($currentHistory[$pageTitle])) {
            unset($currentHistory[$pageTitle]);
        }

        // Add it!
        $currentHistory[$pageTitle] = [
            'name' => $pageTitle,
            'info' => 'Seen: ' . Carbon::now()->format('M j, Y @ g:i A'),
            'url' => Request::fullUrl(),
        ];

        // Do we have more than the max results available?
        if (($totalHistory = count($currentHistory)) > static::MAX_HISTORY_RESULTS) {
            // Remove the results that exceed the max
            $currentHistory = array_slice($currentHistory, $totalHistory - static::MAX_HISTORY_RESULTS, null, true);
        }

        Session::put('alfred.history', $currentHistory);
    }
}
