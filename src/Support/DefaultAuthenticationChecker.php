<?php

namespace Startselect\Alfred\Support;

use Illuminate\Support\Facades\Auth;
use Startselect\Alfred\Contracts\AuthenticationChecker;

class DefaultAuthenticationChecker implements AuthenticationChecker
{
    public function getId(): int
    {
        $authenticatedUser = Auth::user();

        // Check if the auth config's model setting, has a getId method
        if (method_exists($authenticatedUser, 'getId')) {
            return $authenticatedUser->getId();
        }

        // Check if the auth config's model setting, has an ID property
        if (isset($authenticatedUser->id)) {
            return $authenticatedUser->id;
        }

        return 0;
    }
}
