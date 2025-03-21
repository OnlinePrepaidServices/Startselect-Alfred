<?php

namespace Startselect\Alfred\Preparations\Other;

use Startselect\Alfred\Preparations\AbstractPreparation;

class WorkflowStep extends AbstractPreparation
{
    protected ?string $class = null;
    protected string $method = \Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep::METHOD_HANDLE;
    protected ?array $data = null;
    protected array $includeLocalStorageKeys = [];

    protected array $returnableProperties = [
        'class',
        'method',
        'data',
        'includeLocalStorageKeys',
    ];

    /**
     * The class of the workflow step.
     */
    public function class(string $class): static
    {
        $this->class = $class;

        return $this;
    }

    /**
     * The method call of the workflow step.
     */
    public function method(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * The data that will be given to the workflow step.
     */
    public function data(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * The local storage data by giving keys that should be included when triggering the workflow step.
     */
    public function includeLocalStorageKeys(array $includeLocalStorageKeys): static
    {
        $this->includeLocalStorageKeys = $includeLocalStorageKeys;

        return $this;
    }
}
