<?php

namespace Startselect\Alfred\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\ValueObjects\AlfredData;
use Startselect\Alfred\ValueObjects\PageData;

class AlfredWorkflowStepHandlerController extends Controller
{
    public function __invoke(Request $request, Alfred $alfred): JsonResponse
    {
        $request->validate([
            'alfred' => ['required'],
            'alfred.workflowStep' => ['required'],
            'alfred.workflowStep.class' => ['required'],
            'alfred.workflowStep.method' => ['required'],
        ]);

        return new JsonResponse([
            'result' => $alfred->handleWorkflowStep(
                alfredData: new AlfredData($request->get('alfred', [])),
                pageData: new PageData($request->get('page', [])),
            )->toArray(),
        ]);
    }
}
