<?php

namespace Startselect\Alfred\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\Concerns\PreferenceManagerDataSync;
use Startselect\Alfred\Contracts\PreferenceManager;
use Startselect\Alfred\ValueObjects\PageData;

class AlfredInitiationController extends Controller
{
    use PreferenceManagerDataSync;

    public function __invoke(Request $request, Alfred $alfred, PreferenceManager $preferenceManager): JsonResponse
    {
        // Sync data with the preference manager
        $this->syncDataByRequest($preferenceManager, $request);

        return new JsonResponse([
            'result' => $alfred->getRegisteredWorkflowSteps(
                pageData: new PageData($request->input('page', [])),
            )->toArray(),
            'settings' => $preferenceManager->settings()->data,
            'snippets' => $preferenceManager->snippets()->data,
            'storage' => $this->getDataForResponse($preferenceManager),
        ]);
    }
}
