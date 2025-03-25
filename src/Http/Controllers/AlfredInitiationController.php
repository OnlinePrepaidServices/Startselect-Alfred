<?php

namespace Startselect\Alfred\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\Contracts\PreferenceManager;
use Startselect\Alfred\ValueObjects\PageData;

class AlfredInitiationController extends Controller
{
    public function __invoke(
        Request $request,
        Alfred $alfred,
        PreferenceManager $preferenceManager,
    ): JsonResponse
    {
        return new JsonResponse([
            'result' => $alfred->getRegisteredWorkflowSteps(
                pageData: new PageData($request->get('page', [])),
            )->toArray(),
            'snippets' => $preferenceManager->snippets()->data,
        ]);
    }
}
