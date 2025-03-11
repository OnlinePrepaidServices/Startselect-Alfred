<?php

namespace Startselect\Alfred\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\Support\AlfredPreferenceManager;
use Startselect\Alfred\ValueObjects\PageData;

class AlfredInitiationController extends Controller
{
    public function __invoke(
        Request $request,
        Alfred $alfred,
        AlfredPreferenceManager $preferenceManager,
    ): JsonResponse
    {
        return new JsonResponse([
            'result' => $alfred->getRegisteredWorkflowSteps(
                pageData: new PageData($request->get('page', [])),
            )->toArray(),
            'settings' => array_merge(Config::get('alfred.settings'), $preferenceManager->settings()->data),
            'snippets' => $preferenceManager->snippets()->data,
        ]);
    }
}
