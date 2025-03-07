<?php

namespace Startselect\Alfred\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\Http\Requests\AlfredRequest;

class AlfredWorkflowStepHandlerController extends Controller
{
    public function __invoke(AlfredRequest $request, Alfred $alfred): JsonResponse
    {
        $request->validate([
            'alfred' => ['required'],
            'alfred.workflowStep' => ['required'],
            'alfred.workflowStep.class' => ['required'],
            'alfred.workflowStep.method' => ['required'],
        ]);

        return new JsonResponse([
            'result' => $alfred->handleWorkflowStep(
                alfredData: $request->getAlfredData(),
                pageData: $request->getPageData(),
            )->toArray(),
        ]);
    }
}
