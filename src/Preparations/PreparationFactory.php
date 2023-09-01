<?php

namespace Startselect\Alfred\Preparations;

use Startselect\Alfred\Preparations\Other\Redirect;
use Startselect\Alfred\Preparations\Other\ReloadState;

class PreparationFactory
{
    /**
     * Create 'Redirect' preparation.
     *
     * @param string $url
     * @param string $window
     * @param string $type
     *
     * @return Redirect
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
     *
     * @param int $steps
     *
     * @return ReloadState
     */
    public static function reloadState(int $steps = ReloadState::GO_BACK_ONE_STEP): ReloadState
    {
        return (new ReloadState)
            ->steps($steps);
    }
}
