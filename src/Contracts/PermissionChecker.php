<?php

namespace Startselect\Alfred\Contracts;

interface PermissionChecker
{
    /**
     * Get available or known permissions.
     */
    public function getPermissions(): array;

    /**
     * Find a permission by a search string, from the available or known permissions.
     */
    public function findPermission(string $searchString): mixed;

    /**
     * Whether the authenticated user has a specific permission.
     */
    public function hasPermission(mixed $requiredPermission): bool;
}
