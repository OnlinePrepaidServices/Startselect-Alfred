<?php

namespace Startselect\Alfred\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Startselect\Alfred\Contracts\PreferenceManager;

class AlfredSettingManager
{
    public static function init(): array
    {
        /** @var PreferenceManager $preferenceManager */
        $preferenceManager = App::make(PreferenceManager::class);

        return array_merge(Config::get('alfred.settings'), $preferenceManager->settings()->data);
    }
}