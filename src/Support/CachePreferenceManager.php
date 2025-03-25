<?php

namespace Startselect\Alfred\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Startselect\Alfred\Contracts\AuthenticationChecker;
use Startselect\Alfred\Contracts\PreferenceManager;
use Startselect\Alfred\Enums\AlfredPreferenceType;
use Startselect\Alfred\Support\AlfredPreference as AlfredPreference;

class CachePreferenceManager implements PreferenceManager
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
        try {
            Cache::rememberForever($this->getCacheKey($preference->type), function () use ($preference) {
                return $preference;
            });

            return true;
        } catch (\Throwable) {
            return false;
        }
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
        // Did we already get all the preferences from the cache?
        if ($this->preferences) {
            return $this->preferences;
        }

        $this->preferences = new Collection();

        // Create all the preferences in the collection that we have available
        foreach (AlfredPreferenceType::cases() as $type) {
            // Try to get the preference from the cache
            $preference = Cache::get($this->getCacheKey($type));

            if (!$preference) {
                // Add a new preference to the collection instead
                $preference = new AlfredPreference(
                    ownerId: $this->authenticationChecker->getId(),
                    type: $type,
                    data: [],
                );
            }

            $this->preferences->put($type->value, $preference);
        }

        return $this->preferences;
    }

    protected function getCacheKey(AlfredPreferenceType $type): string
    {
        return "alfred-preferences-{$type->value}-{$this->authenticationChecker->getId()}";
    }
}
