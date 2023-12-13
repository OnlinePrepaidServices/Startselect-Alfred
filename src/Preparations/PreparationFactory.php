<?php

namespace Startselect\Alfred\Preparations;

use Startselect\Alfred\Preparations\Other\Redirect;
use Startselect\Alfred\Preparations\Other\ReloadState;

class PreparationFactory
{
    /**
     * Create 'Redirect' preparation.
     */
    public static function redirect(string $url, string $window = Redirect::WINDOW_SAME, string $type = Redirect::TYPE_REGULAR): Redirect
    {
        return (new Redirect)
            ->url($url)
            ->window($window)
            ->type($type);
    }

    /**
     * Create 'ReloadState' preparation.
     */
    public static function reloadState(int $steps = ReloadState::GO_BACK_ONE_STEP): ReloadState
    {
        return (new ReloadState)
            ->steps($steps);
    }
}
