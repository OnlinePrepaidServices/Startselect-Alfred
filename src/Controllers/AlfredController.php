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

class AlfredController extends Controller
{
    /**
     * Initiate Alfred.
     */
    public function initiate(Alfred $alfred, Request $request): JsonResponse
    {
        return response()->json(
            $alfred->getRegisteredWorkflowSteps(
                new PageData($request->get('page'))
            )->toArray()
        );
    }

    /**
     * Handle a workflow step.
     */
    public function handleWorkflowStep(Alfred $alfred, Request $request): JsonResponse
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
