<?php

namespace Startselect\Alfred\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class AlfredSettingManager
{
    public static function init(): array
    {
        /** @var DefaultPreferenceManager $preferenceManager */
        $preferenceManager = App::make(DefaultPreferenceManager::class);

        return array_merge(Config::get('alfred.settings'), $preferenceManager->settings()->data);
    }
}