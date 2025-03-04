<?php

namespace Startselect\Alfred\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\ValueObjects\PageData;

class AlfredInitiationController extends Controller
{
    public function __invoke(Alfred $alfred, Request $request): JsonResponse
    {
        return response()->json(
            $alfred->getRegisteredWorkflowSteps(
                new PageData($request->get('page'))
            )->toArray()
        );
    }
}
