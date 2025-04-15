<?php

namespace Startselect\Alfred\Concerns;

use Illuminate\Http\Request;
use Startselect\Alfred\Contracts\PreferenceManager;
use Startselect\Alfred\Support\PreferenceManagers\LocalStoragePreferenceManager;

trait PreferenceManagerDataSync
{
    /**
     * Update preferences based on the request.
     */
    public function syncDataByRequest(PreferenceManager $preferenceManager, Request $request): void
    {
        // Only update in case of local storage
        if (!$preferenceManager instanceof LocalStoragePreferenceManager) {
            return;
        }

        // Do we have the request information available?
        if (!$request->has('storage')) {
            return;
        }

        // Update preferences
        if ($request->has('storage.itemSettings')) {
            $preferenceManager->itemSettings()->data = $request->input('storage.itemSettings');
        }
        if ($request->has('storage.settings')) {
            $preferenceManager->settings()->data = $request->input('storage.settings');
        }
        if ($request->has('storage.snippets')) {
            $preferenceManager->snippets()->data = $request->input('storage.snippets');
        }
    }

    public function getDataForResponse(PreferenceManager $preferenceManager): array
    {
        // Only return preferences in case of local storage
        if (!$preferenceManager instanceof LocalStoragePreferenceManager) {
            return [];
        }

        return [
            'itemSettings' => $preferenceManager->itemSettings()->data,
            'settings' => $preferenceManager->settings()->data,
            'snippets' => $preferenceManager->snippets()->data,
        ];
    }
}
