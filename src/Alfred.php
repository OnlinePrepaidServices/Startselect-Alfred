<?php

namespace Startselect\Alfred;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;
use Startselect\Alfred\ValueObjects\AlfredData;
use Startselect\Alfred\ValueObjects\PageData;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

class Alfred
{
    public function __construct(
        protected WorkflowStepProvider $workflowStepProvider
    ) {
        //
    }

    public function getRegisteredWorkflowSteps(PageData $pageData): Response
    {
        $itemSet = (new ItemSet())
            ->updateItemsWithItemSettings(true);

        foreach ($this->workflowStepProvider->register() as $workflowStep) {
            // Prepare the workflow step with given data
            $workflowStep->setPageData($pageData);

            $workflowStep->register($itemSet);
        }

        return (new Response())
            ->placeholder(Config::get('alfred.settings.defaultValues.placeholder', 'Find actions..'))
            ->tips(Config::get('alfred.tips', []))
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
                return $response
                    ->success(false)
                    ->notification("Could not find workflow step `{$class}`.");
            }
        }

        $workflowStep = $this->workflowStepProvider->boot($class);

        // Did we get an allowed workflow step?
        if (!$workflowStep->isAllowed()) {
            return $response
                ->success(false)
                ->notification(sprintf(
                    'You have no permission for `%s`.',
                    Str::afterLast($class, '\\')
                ));
        }

        // Did we get a valid method?
        if (
            !method_exists($workflowStep, $method)
            || (new \ReflectionMethod($workflowStep, $method))->class === AbstractWorkflowStep::class
        ) {
            return $response
                ->success(false)
                ->notification(sprintf(
                    'Could not find method `%s` on `%s`.',
                    $method,
                    Str::afterLast($class, '\\')
                ));
        }

        // Prepare the workflow step with given data
        $workflowStep->setAlfredData($alfredData);
        $workflowStep->setPageData($pageData);

        return $workflowStep->$method();
    }
}
