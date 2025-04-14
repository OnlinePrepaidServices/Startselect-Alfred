<?php

namespace Startselect\Alfred\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Startselect\Alfred\Concerns\PreferenceManagerDataSync;
use Startselect\Alfred\Contracts\PreferenceManager;

class AlfredItemSettingsController extends Controller
{
    use PreferenceManagerDataSync;

    public function __invoke(Request $request, PreferenceManager $preferenceManager): JsonResponse
    {
        $request->validate([
            'item' => ['required'],
            'item.name' => ['required'],
            'item.shortcut' => ['nullable', 'array'],
        ]);

        // Sync data with the preference manager
        $this->syncDataByRequest($preferenceManager, $request);

        // Save the item settings
        $preference = $preferenceManager->itemSettings();
        $preference->setData($request->input('item.name'), [
            'shortcut' => $request->input('item.shortcut'),
        ]);

        return new JsonResponse([
            'success' => $preferenceManager->save($preference),
            'storage' => $this->getDataForResponse($preferenceManager),
        ]);
    }
}
