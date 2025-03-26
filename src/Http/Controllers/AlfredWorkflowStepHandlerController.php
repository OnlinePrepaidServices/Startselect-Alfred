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
            if ($request->get('storage.settings')) {
                $preferenceManager->settings()->data = $request->get('storage.settings');
            }
            if ($request->get('storage.snippets')) {
                $preferenceManager->snippets()->data = $request->get('storage.snippets');
            }
        }

        return new JsonResponse([
            'result' => $alfred->handleWorkflowStep(
                alfredData: new AlfredData($request->get('alfred', [])),
                pageData: new PageData($request->get('page', [])),
            )->toArray(),
            'storage' => $preferenceManager instanceof LocalStoragePreferenceManager
                ? $preferenceManager->all()->toArray()
                : [],
        ]);
    }
}
