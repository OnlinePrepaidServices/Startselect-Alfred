<?php

namespace Startselect\Alfred\Support;

use Illuminate\Support\Collection;
use Startselect\Alfred\Contracts\AuthenticationChecker;
use Startselect\Alfred\Enums\AlfredPreferenceType;
use Startselect\Alfred\Models\AlfredPreference;

class AlfredPreferenceManager
{
    protected ?Collection $preferences = null;

    public function __construct(
        protected AuthenticationChecker $authenticationChecker,
    ) {
        //
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
            return $preference->save();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Get an Alfred preference for the authenticated user.
     */
    public function find(AlfredPreferenceType $type): AlfredPreference
    {
        $preference = $this->all()->first(function (AlfredPreference $preference) use ($type) {
            return $preference->type === $type;
        });

        if (!$preference) {
            $preference = new AlfredPreference();
            $preference->owner_id = $this->authenticationChecker->getId();
            $preference->type = $type;
            $preference->data = [];
        }

        return $preference;
    }

    /**
     * Get all Alfred preferences for the authenticated user.
     */
    public function all(): Collection
    {
        if ($this->preferences) {
            return $this->preferences;
        }

        try {
            return $this->preferences = AlfredPreference::query()
                ->where('owner_id', $this->authenticationChecker->getId())
                ->get();
        } catch (\Throwable) {
            // Don't do anything.
        }

        return $this->preferences = new Collection();
    }
}