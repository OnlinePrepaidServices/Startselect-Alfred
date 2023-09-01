<?php

namespace Startselect\Alfred\ValueObjects;

class FocusableField extends AbstractValueObject
{
    public function getId(): string
    {
        return $this->get('id');
    }

    public function getName(): string
    {
        return $this->get('name');
    }
}
