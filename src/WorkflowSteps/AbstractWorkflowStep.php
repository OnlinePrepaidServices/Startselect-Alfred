<?php

namespace Startselect\Alfred\WorkflowSteps;

use Startselect\Alfred\Contracts\PermissionChecker;
use Startselect\Alfred\Contracts\PreferenceManager;
use Startselect\Alfred\Contracts\WorkflowStep;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\ValueObjects\AlfredData;
use Startselect\Alfred\ValueObjects\PageData;

abstract class AbstractWorkflowStep implements WorkflowStep
{
    public const METHOD_INIT = 'init';
    public const METHOD_HANDLE = 'handle';

    protected AlfredData $alfredData;
    protected PageData $pageData;

    /**
     * Required permission that will be used by the isCallable function.
     */
    protected mixed $requiredPermission = null;

    /**
     * Required data with indexes and failure messages.
     *
     * Pseudocode:
     * [
     *   'id' => 'Missing ID.',
     *   'label' => 'Missing label.',
     * ]
     */
    protected array $requiredData = [];
    private array $requiredDataValues = [];
    private array $optionalDataValues = [];
    private ?string $requiredDataFailureMessage = null;

    private Response $response;

    public function __construct(
        protected PermissionChecker $permissionChecker,
        protected PreferenceManager $preferenceManager,
    ) {
        $this->response = new Response();
    }

    public function register(ItemSet $itemSet): void
    {
    }

    public function init(): Response
    {
        return $this->getResponse()->success(false);
    }

    public function handle(): Response
    {
        return $this->getResponse()->success(false);
    }

    /**
     * Set this workflow step's Alfred data.
     *
     * This prepares the workflow step, so it has easy access to required and optional data.
     */
    final public function setAlfredData(AlfredData $alfredData): void
    {
        $this->alfredData = $alfredData;

        // Is there any data at all?
        $workflowStepData = $alfredData->getWorkflowStep()->getData();
        if (!$workflowStepData) {
            return;
        }

        // Map data to required or optional data
        foreach ($workflowStepData as $key => $value) {
            // Required data?
            if (isset($this->requiredData[$alfredData->getWorkflowStep()->getMethod()][$key])) {
                $this->requiredDataValues[$key] = $value;

                continue;
            }

            // Optional
            $this->optionalDataValues[$key] = $value;
        }
    }

    /**
     * Set this workflow step's page data.
     */
    final public function setPageData(PageData $pageData): void
    {
        $this->pageData = $pageData;
    }

    /**
     * Whether this workflow step is allowed to be handled.
     */
    final public function isAllowed(): bool
    {
        return $this->permissionChecker->hasPermission($this->requiredPermission);
    }

    /**
     * Workflow step failed somewhere.
     */
    final protected function failure(string $message = '', string $notification = ''): Response
    {
        // Did we receive a message?
        if ($message) {
            $this->getResponse()->message($message);
        }

        // Did we receive a notification?
        if ($notification) {
            $this->getResponse()->notification($notification);
        }

        // Did we get a required data failure message?
        if ($this->requiredDataFailureMessage) {
            $this->getResponse()->message($this->requiredDataFailureMessage);
        }

        return $this->getResponse()->success(false);
    }

    /**
     * Get the current response.
     */
    final protected function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Get the value of optional data; or all data.
     */
    final protected function getOptionalData(?string $key = null): mixed
    {
        // Return all data?
        if ($key === null) {
            return $this->optionalDataValues;
        }

        return $this->optionalDataValues[$key] ?? null;
    }

    /**
     * Get the value of required data; or all data.
     */
    final protected function getRequiredData(?string $key = null): mixed
    {
        // Return all data?
        if ($key === null) {
            return $this->requiredDataValues;
        }

        return $this->requiredDataValues[$key] ?? null;
    }

    /**
     * Whether all required data is available.
     */
    final protected function isRequiredDataPresent(string $method): bool
    {
        // Is there any data required at all for this method?
        if (empty($this->requiredData[$method])) {
            return true;
        }

        foreach ($this->requiredData[$method] as $dataIndex => $failureMessage) {
            // Does the required data exist?
            if (!array_key_exists($dataIndex, $this->requiredDataValues)) {
                $this->requiredDataFailureMessage = $failureMessage;

                return false;
            }
        }

        return true;
    }
}
