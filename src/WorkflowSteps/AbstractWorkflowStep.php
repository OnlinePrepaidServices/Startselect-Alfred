<?php

namespace Startselect\Alfred\WorkflowSteps;

use Illuminate\Support\Facades\App;
use Startselect\Alfred\Contracts\PermissionChecker;
use Startselect\Alfred\Contracts\WorkflowStep;
use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\ValueObjects\AlfredData;
use Startselect\Alfred\ValueObjects\PageData;

abstract class AbstractWorkflowStep implements WorkflowStep
{
    public const METHOD_INIT = 'init';
    public const METHOD_HANDLE = 'handle';

    /**
     * Required permission that will be used by the isCallable function.
     */
    protected mixed $requiredPermission = null;

    /**
     * Blacklist of URL paths.
     *
     * Alfred doesn't allow to register this workflow step based on these (partly) given URL paths.
     */
    protected array $blacklistUrlPaths = [];

    /**
     * Whitelist of URL paths.
     *
     * Alfred does allow to register this workflow step based on these (partly) given URL paths.
     */
    protected array $whitelistUrlPaths = [];

    protected AlfredData $alfredData;
    protected PageData $pageData;

    /**
     * Required data with indexes and failure messages.
     *
     * Pseudo code:
     * [
     *   'id' => 'Missing ID.',
     *   'label' => 'Missing label.',
     * ]
     */
    protected array $requiredData = [];
    private array $requiredDataValues = [];
    private array $optionalDataValues = [];
    private ?string $requiredDataFailureMessage = null;

    private array $parametersInUrlPath = [];

    private Response $response;

    public function __construct()
    {
        $this->response = new Response();
    }

    public function register(): Item|array|null
    {
        return null;
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
        /** @var PermissionChecker $permissionChecker */
        $permissionChecker = App::make(PermissionChecker::class);

        return $permissionChecker->hasPermission($this->requiredPermission);
    }

    /**
     * Whether this workflow step is registrable based on the current page data.
     */
    final public function isRegistrable(): bool
    {
        // Did we receive a URL path?
        if (!$urlPath = $this->pageData->getUrl()->getPath()) {
            return true;
        }

        // Did we define any URL paths?
        if (empty($this->blacklistUrlPaths) && empty($this->whitelistUrlPaths)) {
            return true;
        }

        // Blacklisted?
        if ($this->isUrlPathInList($urlPath, $this->blacklistUrlPaths)) {
            return false;
        }

        // Whitelisted?
        if ($this->isUrlPathInList($urlPath, $this->whitelistUrlPaths)) {
            return true;
        }

        return false;
    }

    /**
     * Workflow step failed somewhere.
     */
    protected function failure(string $message = ''): Response
    {
        // Did we receive a message?
        if ($message) {
            $this->getResponse()->message($message);
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
    protected function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Get a parameter from the URL path that was accepted by the whitelist.
     */
    protected function getParameterFromUrlPath(string $name): ?string
    {
        return $this->parametersInUrlPath[$name] ?? null;
    }

    /**
     * Get the value of optional data; or all data.
     */
    protected function getOptionalData(?string $key = null): mixed
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
    protected function getRequiredData(?string $key = null): mixed
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
    protected function isRequiredDataPresent(string $method): bool
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

    /**
     * Whether the URL path is found in the given list.
     */
    private function isUrlPathInList(string $path, array $pathsToCheck): bool
    {
        foreach ($pathsToCheck as $pathToCheck) {
            // Normal path?
            if (str_contains($path, $pathToCheck)) {
                return true;
            }

            // Regex path?
            if (str_contains($pathToCheck, '(') || str_contains($pathToCheck, '[')) {
                // Make sure our pattern is correct
                $pattern = str_replace('/', "\/", $pathToCheck);

                if (preg_match("/{$pattern}/", $path, $matches)) {
                    // Make sure we can get the parameters from this path
                    $this->parametersInUrlPath = $matches;

                    return true;
                }
            }
        }

        return false;
    }
}
