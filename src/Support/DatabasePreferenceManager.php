<?php

namespace Startselect\Alfred\Support;

use Illuminate\Support\Collection;
use Startselect\Alfred\Contracts\AuthenticationChecker;
use Startselect\Alfred\Contracts\PreferenceManager;
use Startselect\Alfred\Enums\AlfredPreferenceType;
use Startselect\Alfred\Models\AlfredPreference as AlfredPreferenceModel;
use Startselect\Alfred\Support\AlfredPreference as AlfredPreference;

class DatabasePreferenceManager implements PreferenceManager
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
            // Find existing preference in DB
            $model = AlfredPreferenceModel::query()
                ->where('owner_id', $preference->ownerId)
                ->where('type', $preference->type)
                ->first();

            if ($model) {
                $model->data = $preference->data;

                return $model->save();
            }

            // Create new record in DB
            $model = new AlfredPreferenceModel();
            $model->owner_id = $preference->ownerId;
            $model->type = $preference->type;
            $model->data = $preference->data;

            return $model->save();
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

        $this->preferences = new Collection();

        try {
            // Get all known preferences from the DB
            $preferences = AlfredPreferenceModel::query()
                ->where('owner_id', $this->authenticationChecker->getId())
                ->get();

            foreach ($preferences as $modelPreference) {
                $preference = new AlfredPreference(
                    ownerId: $modelPreference->owner_id,
                    type: $modelPreference->type,
                    data: $modelPreference->data,
                );

                $this->preferences->put($modelPreference->type->value, $preference);
            }
        } catch (\Throwable) {
            // Do nothing
        }

        // Create all the preferences in the collection that we have available
        foreach (AlfredPreferenceType::cases() as $type) {
            if (!$this->preferences->has($type->value)) {
                $preference = new AlfredPreference(
                    ownerId: $this->authenticationChecker->getId(),
                    type: $type,
                    data: [],
                );

                $this->preferences->put($type->value, $preference);
            }
        }

        return $this->preferences;
    }
}
