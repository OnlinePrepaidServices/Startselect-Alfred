<?php

namespace Startselect\Alfred\Preparations;

use Illuminate\Support\Facades\App;
use Startselect\Alfred\Contracts\PermissionChecker;
use Startselect\Alfred\Contracts\Preparation as PreparationInterface;

abstract class AbstractPreparation implements PreparationInterface
{
    /**
     * Required permission that will be used by the isValid function.
     */
    protected mixed $requiredPermission = null;

    /**
     * Properties that will be used by the isValid function.
     *
     * When nothing is defined, all arrayableProperties will be used instead.
     */
    protected array $validationProperties = [];

    /**
     * Properties that will be returned by the toArray function.
     */
    protected array $returnableProperties = [];

    /**
     * Get a property.
     */
    protected function getProperty(string $property): mixed
    {
        return $this->$property;
    }

    /**
     * The required permission that is checked when validating the preparation.
     */
    public function requiresPermission(mixed $requiredPermission): static
    {
        $this->requiredPermission = $requiredPermission;

        return $this;
    }

    /**
     * Apply the callback if the value is truthy.
     */
    public function when(mixed $value, ?callable $callback = null, ?callable $default = null): static
    {
        if ($value) {
            $callback($this, $value);
        } elseif ($default) {
            $default($this, $value);
        }

        return $this;
    }

    public function isValid(): bool
    {
        /** @var PermissionChecker $permissionChecker */
        $permissionChecker = App::make(PermissionChecker::class);

        // Specific permission required?
        if ($this->requiredPermission !== null && !$permissionChecker->hasPermission($this->requiredPermission)) {
            return false;
        }

        // Specific validation properties or everything that is eventually returned?
        $properties = !empty($this->validationProperties) ? $this->validationProperties : $this->returnableProperties;

        foreach ($properties as $property) {
            if ($this->$property === null) {
                return false;
            }
        }

        return true;
    }

    public function toArray(): array
    {
        $data = [
            'type' => class_basename(static::class),
            'properties' => [],
        ];

        foreach ($this->returnableProperties as $property) {
            $data['properties'][$property] = $this->getProperty($property);
        }

        return $data;
    }
}
