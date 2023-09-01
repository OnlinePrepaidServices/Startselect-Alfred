<?php

namespace Startselect\Alfred\Contracts;

interface PermissionChecker
{
    /**
     * Get available or known permissions.
     *
     * @return array
     */
    public function getPermissions(): array;

    /**
     * Find a permission by a search string, from the available or known permissions.
     *
     * @param string $searchString
     *
     * @return string|null
     */
    public function findPermission(string $searchString): ?string;

    /**
     * Whether the authenticated user has a specific permission.
     *
     * @param string|array|null $requiredPermission
     *
     * @return bool
     */
    public function hasPermission(mixed $requiredPermission): bool;
}
