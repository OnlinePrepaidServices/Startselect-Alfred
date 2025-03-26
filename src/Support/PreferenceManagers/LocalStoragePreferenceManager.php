<?php

namespace Startselect\Alfred\Support\PreferenceManagers;

use Illuminate\Support\Collection;
use Startselect\Alfred\Enums\AlfredPreferenceType;
use Startselect\Alfred\Support\AlfredPreference as AlfredPreference;

class LocalStoragePreferenceManager extends AbstractPreferenceManager
{
    public function save(AlfredPreference $preference): bool
    {
        return true;
    }

    public function all(): Collection
    {
        // Did we already get all the preferences?
        if ($this->preferences) {
            return $this->preferences;
        }

        $this->preferences = new Collection();

        // Create all the preferences in the collection that we have available
        foreach (AlfredPreferenceType::cases() as $type) {
            // Add a new preference to the collection instead
            $preference = new AlfredPreference(
                ownerId: $this->authenticationChecker->getId(),
                type: $type,
                data: [],
            );

            $this->preferences->put($type->value, $preference);
        }

        return $this->preferences;
    }
}
