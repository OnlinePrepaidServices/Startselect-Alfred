<?php

use Startselect\Alfred\WorkflowSteps as CoreWorkflowSteps;

return [
    // List of workflow steps that will add one or more items to the opening response of Alfred
    'registerWorkflowSteps' => [
        CoreWorkflowSteps\Auth\Logout::class,
        CoreWorkflowSteps\Routing\BasicRoutes::class,
        CoreWorkflowSteps\Routing\History::class,
        CoreWorkflowSteps\Snippets\CreateSnippet::class,
        CoreWorkflowSteps\Snippets\DeleteSnippet::class,
        CoreWorkflowSteps\Snippets\EditSnippet::class,
        CoreWorkflowSteps\Snippets\ExecuteSnippet::class,
    ],

    // List of workflow steps that can be processed, once called upon by Alfred
    'optionalWorkflowSteps' => [],

    // The permission checker that'll be used when handling preparations and workflow steps required permissions
    'permissionChecker' => Startselect\Alfred\Support\DefaultPermissionChecker::class,
];
