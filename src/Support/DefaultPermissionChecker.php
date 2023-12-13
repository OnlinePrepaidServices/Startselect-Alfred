<?php

namespace Startselect\Alfred\Support;

use Startselect\Alfred\Contracts\PermissionChecker;

class DefaultPermissionChecker implements PermissionChecker
{
    public function getPermissions(): array
    {
        return [];
    }

    public function findPermission(mixed $searchPermission): mixed
    {
        return null;
    }

    public function hasPermission(mixed $requiredPermission): bool
    {
        return true;
    }
}
