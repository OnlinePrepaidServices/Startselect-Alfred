<?php

namespace Startselect\Alfred\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class AlfredSettingManager
{
    public static function init(): array
    {
        /** @var AlfredPreferenceManager $preferenceManager */
        $preferenceManager = App::make(AlfredPreferenceManager::class);

        return array_merge(Config::get('alfred.settings'), $preferenceManager->settings()->data);
    }
}