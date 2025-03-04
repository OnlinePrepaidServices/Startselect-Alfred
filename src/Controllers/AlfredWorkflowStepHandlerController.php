<?php

namespace Startselect\Alfred\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\ValueObjects\AlfredData;
use Startselect\Alfred\ValueObjects\PageData;

class AlfredWorkflowStepHandlerController extends Controller
{
    public function __invoke(Alfred $alfred, Request $request): JsonResponse
    {
        // Get request information
        $alfredData = $request->get('alfred');
        $pageData = $request->get('page');

        // Did we get a workflow step?
        if (!Arr::get($alfredData, 'workflowStep.class') || !Arr::get($alfredData, 'workflowStep.method')) {
            return response()->json([], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(
            $alfred->handleWorkflowStep(
                new AlfredData($alfredData),
                new PageData($pageData)
            )->toArray()
        );
    }
}
