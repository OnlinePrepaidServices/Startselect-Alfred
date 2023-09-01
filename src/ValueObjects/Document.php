<?php

namespace Startselect\Alfred\ValueObjects;

class Document extends AbstractValueObject
{
    public function getTitle(): string
    {
        return $this->get('title') ?? '';
    }
}
