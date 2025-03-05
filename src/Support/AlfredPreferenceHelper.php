<?php

namespace Startselect\Alfred\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Startselect\Alfred\Contracts\AuthenticationChecker;
use Startselect\Alfred\Enums\AlfredPreferenceType;
use Startselect\Alfred\Models\AlfredPreference;

class AlfredPreferenceHelper
{
    protected static ?Collection $preferences;

    /**
     * Get snippets for the authenticated user.
     */
    public static function snippets(): AlfredPreference
    {
        return static::find(AlfredPreferenceType::SNIPPETS);
    }

    /**
     * Get an Alfred preference for the authenticated user.
     */
    public static function find(AlfredPreferenceType $type): AlfredPreference
    {
        $preference = static::all()->first(function (AlfredPreference $preference) use ($type) {
            return $preference->type === $type;
        });

        if (!$preference) {
            /** @var AuthenticationChecker $authenticationChecker */
            $authenticationChecker = App::make(AuthenticationChecker::class);

            $preference = new AlfredPreference();
            $preference->owner_id = $authenticationChecker->getId();
            $preference->type = $type;
            $preference->data = [];
        }

        return $preference;
    }

    /**
     * Get all Alfred preferences for the authenticated user.
     */
    public static function all(): Collection
    {
        if (static::$preferences) {
            return static::$preferences;
        }

        try {
            /** @var AuthenticationChecker $authenticationChecker */
            $authenticationChecker = App::make(AuthenticationChecker::class);

            static::$preferences = AlfredPreference::query()
                ->where('owner_id', $authenticationChecker->getId())
                ->get();
        } catch (\Throwable) {
            // Don't do anything.
        }

        return static::$preferences = new Collection();
    }
}