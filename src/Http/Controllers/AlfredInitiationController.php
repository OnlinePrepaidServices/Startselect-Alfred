<?php

namespace Startselect\Alfred\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\Contracts\PreferenceManager;
use Startselect\Alfred\ValueObjects\PageData;
use Startselect\Alfred\WorkflowSteps\Settings\LocalStorage\ManageSettings;

class AlfredInitiationController extends Controller
{
    public function __invoke(
        Request $request,
        Alfred $alfred,
        PreferenceManager $preferenceManager,
    ): JsonResponse
    {
        // Make sure the settings are not overwritten by LocalStorage
        $settings = $preferenceManager->settings()->data;
        if (!$settings) {
            $settings = !in_array(ManageSettings::class, Config::get('alfred.registerWorkflowSteps', []));
        }

        return new JsonResponse([
            'result' => $alfred->getRegisteredWorkflowSteps(
                pageData: new PageData($request->get('page', [])),
            )->toArray(),
            'settings' => $settings,
            'snippets' => $preferenceManager->snippets()->data,
        ]);
    }
}
