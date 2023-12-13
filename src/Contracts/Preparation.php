<?php

namespace Startselect\Alfred\Contracts;

interface Preparation
{
    /**
     * Whether the preparation has enough data to be given to Alfred.
     */
    public function isValid(): bool;

    /**
     * Transform our object to an array so Alfred can work with it.
     */
    public function toArray(): array;
}
