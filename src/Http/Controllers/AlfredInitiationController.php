<?php

namespace Startselect\Alfred\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\Contracts\PreferenceManager;
use Startselect\Alfred\Support\PreferenceManagers\LocalStoragePreferenceManager;
use Startselect\Alfred\ValueObjects\PageData;

class AlfredInitiationController extends Controller
{
    public function __invoke(Request $request, Alfred $alfred, PreferenceManager $preferenceManager): JsonResponse
    {
        // Update local storage preferences
        if ($preferenceManager instanceof LocalStoragePreferenceManager && $request->has('storage')) {
            if ($request->has('storage.settings')) {
                $preferenceManager->settings()->data = $request->input('storage.settings');
            }
            if ($request->has('storage.snippets')) {
                $preferenceManager->snippets()->data = $request->input('storage.snippets');
            }
        }

        return new JsonResponse([
            'result' => $alfred->getRegisteredWorkflowSteps(
                pageData: new PageData($request->input('page', [])),
            )->toArray(),
            'settings' => $preferenceManager->settings()->data,
            'snippets' => $preferenceManager->snippets()->data,
            'storage' => $preferenceManager instanceof LocalStoragePreferenceManager
                ? [
                    'settings' => $preferenceManager->settings()->data,
                    'snippets' => $preferenceManager->snippets()->data,
                ]
                : [],
        ]);
    }
}
