<?php

namespace Startselect\Alfred\Support\PreferenceManagers;

use Illuminate\Support\Collection;
use Startselect\Alfred\Contracts\AuthenticationChecker;
use Startselect\Alfred\Contracts\PreferenceManager;
use Startselect\Alfred\Enums\AlfredPreferenceType;
use Startselect\Alfred\Support\AlfredPreference;

abstract class AbstractPreferenceManager implements PreferenceManager
{
    protected ?Collection $preferences = null;

    public function __construct(
        protected AuthenticationChecker $authenticationChecker,
    ) {
        //
    }

    public function settings(): AlfredPreference
    {
        return $this->find(AlfredPreferenceType::SETTINGS);
    }

    /**
     * Get snippets for the authenticated user.
     */
    public function snippets(): AlfredPreference
    {
        return $this->find(AlfredPreferenceType::SNIPPETS);
    }

    /**
     * Save an Alfred preference.
     */
    public function save(AlfredPreference $preference): bool
    {
        return true;
    }

    /**
     * Get an Alfred preference for the authenticated user.
     */
    public function find(AlfredPreferenceType $type): AlfredPreference
    {
        return $this->all()->first(function (AlfredPreference $preference) use ($type) {
            return $preference->type === $type;
        });
    }

    /**
     * Get all Alfred preferences for the authenticated user.
     */
    public function all(): Collection
    {
        if ($this->preferences === null) {
            $this->preferences = new Collection();
        }

        return $this->preferences;
    }
}
