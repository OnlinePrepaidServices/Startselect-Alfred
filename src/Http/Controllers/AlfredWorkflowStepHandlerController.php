<?php

namespace Startselect\Alfred\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\Concerns\PreferenceManagerDataSync;
use Startselect\Alfred\Contracts\PreferenceManager;
use Startselect\Alfred\ValueObjects\AlfredData;
use Startselect\Alfred\ValueObjects\PageData;

class AlfredWorkflowStepHandlerController extends Controller
{
    use PreferenceManagerDataSync;

    public function __invoke(Request $request, Alfred $alfred, PreferenceManager $preferenceManager): JsonResponse
    {
        $request->validate([
            'alfred' => ['required'],
            'alfred.workflowStep' => ['required'],
            'alfred.workflowStep.class' => ['required'],
            'alfred.workflowStep.method' => ['required'],
        ]);

        // Sync data with the preference manager
        $this->syncDataByRequest($preferenceManager, $request);

        return new JsonResponse([
            'result' => $alfred->handleWorkflowStep(
                alfredData: new AlfredData($request->input('alfred', [])),
                pageData: new PageData($request->input('page', [])),
            )->toArray(),
            'storage' => $this->getDataForResponse($preferenceManager),
        ]);
    }
}
