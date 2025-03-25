<?php

namespace Startselect\Alfred\Contracts;

use Illuminate\Support\Collection;
use Startselect\Alfred\Enums\AlfredPreferenceType;
use Startselect\Alfred\Support\AlfredPreference;

interface PreferenceManager
{
    public function __construct(
        AuthenticationChecker $authenticationChecker,
    );

    /**
     * Get settings for the authenticated user.
     */
    public function settings(): AlfredPreference;

    /**
     * Get snippets for the authenticated user.
     */
    public function snippets(): AlfredPreference;

    /**
     * Save an Alfred preference.
     */
    public function save(AlfredPreference $preference): bool;

    /**
     * Get an Alfred preference for the authenticated user.
     */
    public function find(AlfredPreferenceType $type): AlfredPreference;

    /**
     * Get all Alfred preferences for the authenticated user.
     */
    public function all(): Collection;
}
