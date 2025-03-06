<?php

namespace Startselect\Alfred\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\Support\AlfredPreferenceManager;
use Startselect\Alfred\ValueObjects\PageData;

class AlfredInitiationController extends Controller
{
    public function __invoke(
        Alfred $alfred,
        AlfredPreferenceManager $alfredPreferenceManager,
        Request $request
    ): JsonResponse
    {
        return new JsonResponse([
            'result' => $alfred->getRegisteredWorkflowSteps(pageData: new PageData($request->get('page')))->toArray(),
            'snippets' => $alfredPreferenceManager->snippets()->data,
        ]);
    }
}
