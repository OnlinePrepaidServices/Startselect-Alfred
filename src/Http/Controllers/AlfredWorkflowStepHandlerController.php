<?php

namespace Startselect\Alfred\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\ValueObjects\AlfredData;
use Startselect\Alfred\ValueObjects\PageData;
use Symfony\Component\HttpFoundation\Response;

class AlfredWorkflowStepHandlerController extends Controller
{
    public function __invoke(Alfred $alfred, Request $request): JsonResponse
    {
        // Get request information
        $alfredData = $request->get('alfred');
        $pageData = $request->get('page');

        // Did we get a workflow step?
        if (!Arr::get($alfredData, 'workflowStep.class') || !Arr::get($alfredData, 'workflowStep.method')) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'data' => $alfred->handleWorkflowStep(
                alfredData: new AlfredData($alfredData),
                pageData: new PageData($pageData)
            )->toArray(),
        ]);
    }
}
