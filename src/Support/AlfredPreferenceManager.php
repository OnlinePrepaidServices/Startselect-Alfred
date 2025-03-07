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
        return $this->all()->first(function (AlfredPreference $preference) use ($type) {
            return $preference->type === $type;
        });
    }

    /**
     * Get all Alfred preferences for the authenticated user.
     */
    public function all(): Collection
    {
        // Did we already get all the preferences from the DB?
        if ($this->preferences) {
            return $this->preferences;
        }

        try {
            $this->preferences = AlfredPreference::query()
                ->where('owner_id', $this->authenticationChecker->getId())
                ->get()
                ->groupBy('type');
        } catch (\Throwable) {
            $this->preferences = new Collection();
        }

        // Create all the preferences in the collection that we have available
        foreach (AlfredPreferenceType::cases() as $type) {
            if (!$this->preferences->has($type->value)) {
                $preference = new AlfredPreference();
                $preference->owner_id = $this->authenticationChecker->getId();
                $preference->type = $type;
                $preference->data = [];

                $this->preferences->put($type->value, $preference);
            }
        }

        return $this->preferences;
    }
}