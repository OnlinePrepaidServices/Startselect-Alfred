<?php

namespace Startselect\Alfred\Contracts;

interface AuthenticationChecker
{
    /**
     * Get authenticated user ID.
     */
    public function getId(): int;
}
