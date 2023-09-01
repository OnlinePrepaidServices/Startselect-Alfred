<?php

namespace Startselect\Alfred;

use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\ValueObjects\AlfredData;
use Startselect\Alfred\ValueObjects\PageData;

class Alfred
{
    public function __construct(
        protected WorkflowStepProvider $workflowStepProvider
    ) {
        //
    }

    public function getRegisteredWorkflowSteps(PageData $pageData): Response
    {
        $itemSet = new ItemSet();

        foreach ($this->workflowStepProvider->register() as $workflowStep) {
            // Prepare the workflow step with given data
            $workflowStep->setPageData($pageData);

            // Let's find our items for this workflow step
            if ($workflowStep->isRegistrable() && $registerResult = $workflowStep->register()) {
                // Did we get multiple?
                if (is_array($registerResult)) {
                    $itemSet->addItems($registerResult);
                    continue;
                }

                $itemSet->addItem($registerResult);
            }
        }

        return (new Response())
            ->placeholder('Find actions..')
            ->trigger($itemSet);
    }

    public function handleWorkflowStep(AlfredData $alfredData, PageData $pageData): Response
    {
        $response = new Response();

        $class = $alfredData->getWorkflowStep()->getClass();
        $method = $alfredData->getWorkflowStep()->getMethod();

        // Did we get a bootable workflow step?
        if (!$this->workflowStepProvider->isBootable($class)) {
            // Second chance! Can we find a bootable class by a partial name?
            if ($bootableClass = $this->workflowStepProvider->getBootableClassByPartialName($class)) {
                $class = $bootableClass;
            } else {
                // We really don't have this class..
                return $response;
            }
        }

        $workflowStep = $this->workflowStepProvider->boot($class);

        // Did we get an allowed workflow step?
        if (!$workflowStep->isAllowed()) {
            return $response;
        }

        // Did we get a valid method?
        if (!method_exists($workflowStep, $method)) {
            return $response;
        }

        // Prepare the workflow step with given data
        $workflowStep->setAlfredData($alfredData);
        $workflowStep->setPageData($pageData);

        return $workflowStep->$method();
    }
}
