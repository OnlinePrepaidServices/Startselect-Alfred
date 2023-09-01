<?php

namespace Startselect\Alfred\ValueObjects;

class WorkflowStep extends AbstractValueObject
{
    public function getClass(): string
    {
        return $this->get('class');
    }

    public function getMethod(): string
    {
        return $this->get('method');
    }

    public function getData(): array
    {
        return $this->get('data') ?? [];
    }

    public function getLocalStorageData(string $key): array
    {
        return $this->get('localStorage', [])[$key] ?? [];
    }
}
