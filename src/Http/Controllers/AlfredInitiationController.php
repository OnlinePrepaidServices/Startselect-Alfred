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
    public function __invoke(
        Request $request,
        Alfred $alfred,
        PreferenceManager $preferenceManager,
    ): JsonResponse
    {
        // Update local storage preferences
        if ($preferenceManager instanceof LocalStoragePreferenceManager && $request->has('storage')) {
            if ($request->get('storage.settings')) {
                $preferenceManager->settings()->data = $request->get('storage.settings');
            }
            if ($request->get('storage.snippets')) {
                $preferenceManager->snippets()->data = $request->get('storage.snippets');
            }
        }

        return new JsonResponse([
            'result' => $alfred->getRegisteredWorkflowSteps(
                pageData: new PageData($request->get('page', [])),
            )->toArray(),
            'settings' => $preferenceManager->settings()->data,
            'snippets' => $preferenceManager->snippets()->data,
            'storage' => $preferenceManager instanceof LocalStoragePreferenceManager
                ? $preferenceManager->all()->toArray()
                : [],
        ]);
    }
}
