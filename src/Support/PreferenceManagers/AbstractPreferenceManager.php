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

    public function snippets(): AlfredPreference
    {
        return $this->find(AlfredPreferenceType::SNIPPETS);
    }

    public function itemSettings(): AlfredPreference
    {
        return $this->find(AlfredPreferenceType::ITEM_SETTINGS);
    }

    public function save(AlfredPreference $preference): bool
    {
        return true;
    }

    public function find(AlfredPreferenceType $type): AlfredPreference
    {
        return $this->all()->first(function (AlfredPreference $preference) use ($type) {
            return $preference->type === $type;
        });
    }

    public function all(): Collection
    {
        if ($this->preferences === null) {
            $this->preferences = new Collection();
        }

        return $this->preferences;
    }
}
