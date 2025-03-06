<?php

namespace Startselect\Alfred;

use Startselect\Alfred\Contracts\PermissionChecker;
use Startselect\Alfred\Support\AlfredPreferenceManager;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

class WorkflowStepProvider
{
    protected array $bootedWorkflowSteps = [];

    public function __construct(
        protected PermissionChecker $permissionChecker,
        protected AlfredPreferenceManager $alfredPreferenceManager,
        protected array $registerWorkflowSteps = [],
        protected array $optionalWorkflowSteps = [],
    ) {
        $this->register();
    }

    /**
     * Register Alfred workflow steps.
     *
     * @return array<AbstractWorkflowStep>
     */
    public function register(): array
    {
        foreach ($this->registerWorkflowSteps as $registerWorkflowStep) {
            $this->boot($registerWorkflowStep);
        }

        return $this->bootedWorkflowSteps;
    }

    /**
     * Bootstrap a workflow step.
     */
    public function boot(string $className): AbstractWorkflowStep
    {
        return $this->bootedWorkflowSteps[$className] = new $className(
            $this->permissionChecker,
            $this->alfredPreferenceManager,
        );
    }

    /**
     * Whether a workflow step is bootable.
     */
    public function isBootable(string $className): bool
    {
        return in_array($className, $this->registerWorkflowSteps) || in_array($className, $this->optionalWorkflowSteps);
    }

    /**
     * Get a bootable class by a partial class name.
     */
    public function getBootableClassByPartialName(string $className): ?string
    {
        foreach ($this->registerWorkflowSteps as $registerWorkflowStep) {
            if (str_contains($registerWorkflowStep, $className)) {
                return $registerWorkflowStep;
            }
        }

        foreach ($this->optionalWorkflowSteps as $optionalWorkflowStep) {
            if (str_contains($optionalWorkflowStep, $className)) {
                return $optionalWorkflowStep;
            }
        }

        return null;
    }
}
