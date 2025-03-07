<?php

namespace Startselect\Alfred\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\Http\Requests\AlfredRequest;
use Startselect\Alfred\Support\AlfredPreferenceManager;

class AlfredInitiationController extends Controller
{
    public function __invoke(
        AlfredRequest $request,
        Alfred $alfred,
        AlfredPreferenceManager $preferenceManager,
    ): JsonResponse
    {
        return new JsonResponse([
            'result' => $alfred->getRegisteredWorkflowSteps(
                pageData: $request->getPageData()
            )->toArray(),
            'snippets' => $preferenceManager->snippets()->data,
        ]);
    }
}
