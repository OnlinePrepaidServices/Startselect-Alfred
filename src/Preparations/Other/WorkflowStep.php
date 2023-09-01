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
     *
     * @param string $class
     *
     * @return $this
     */
    public function class(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * The method call of the workflow step.
     *
     * @param string $method
     *
     * @return $this
     */
    public function method(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * The data that will be given to the workflow step.
     *
     * @param array $data
     *
     * @return $this
     */
    public function data(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * The local storage data by giving keys that should be included when triggering the workflow step.
     *
     * @param array $includeLocalStorageKeys
     *
     * @return $this
     */
    public function includeLocalStorageKeys(array $includeLocalStorageKeys): self
    {
        $this->includeLocalStorageKeys = $includeLocalStorageKeys;

        return $this;
    }
}
