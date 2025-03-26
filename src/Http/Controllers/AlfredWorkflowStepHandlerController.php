<?php

namespace Startselect\Alfred\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\Contracts\PreferenceManager;
use Startselect\Alfred\Support\PreferenceManagers\LocalStoragePreferenceManager;
use Startselect\Alfred\ValueObjects\AlfredData;
use Startselect\Alfred\ValueObjects\PageData;

class AlfredWorkflowStepHandlerController extends Controller
{
    public function __invoke(Request $request, Alfred $alfred, PreferenceManager $preferenceManager): JsonResponse
    {
        $request->validate([
            'alfred' => ['required'],
            'alfred.workflowStep' => ['required'],
            'alfred.workflowStep.class' => ['required'],
            'alfred.workflowStep.method' => ['required'],
        ]);

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
            'result' => $alfred->handleWorkflowStep(
                alfredData: new AlfredData($request->input('alfred', [])),
                pageData: new PageData($request->input('page', [])),
            )->toArray(),
            'storage' => $preferenceManager instanceof LocalStoragePreferenceManager
                ? [
                    'settings' => $preferenceManager->settings()->data,
                    'snippets' => $preferenceManager->snippets()->data,
                ]
                : [],
        ]);
    }
}
