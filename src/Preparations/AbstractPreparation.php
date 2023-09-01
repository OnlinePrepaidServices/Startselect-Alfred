<?php

namespace Startselect\Alfred\Preparations;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Startselect\Alfred\Contracts\PermissionChecker;
use Startselect\Alfred\Contracts\Preparation as PreparationInterface;

abstract class AbstractPreparation implements PreparationInterface
{
    /**
     * Required permission that will be used by the isValid function.
     */
    protected string|array|null $requiredPermission = null;

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
     *
     * @param string $property
     *
     * @return mixed
     */
    protected function getProperty(string $property): mixed
    {
        return $this->$property;
    }

    /**
     * The required permission that is checked when validating the preparation.
     *
     * @param array|string $requiredPermission
     *
     * @return $this
     */
    public function requiresPermission(array|string $requiredPermission): self
    {
        $this->requiredPermission = $requiredPermission;

        return $this;
    }

    /**
     * Apply the callback if the value is truthy.
     *
     * @param mixed $value
     * @param callable|null $callback
     * @param callable|null $default
     *
     * @return $this
     */
    public function when(mixed $value, callable $callback = null, callable $default = null): self
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
        $permissionChecker = app(PermissionChecker::class);

        // Specific permission required?
        if (!$permissionChecker->hasPermission($this->requiredPermission)) {
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
