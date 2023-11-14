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

    'settings' => [
        // Which class in the HTML contains inputs that can be focused.
        // Note: needs to contain a label and form input within the given class name.
        'focusableFieldsClass' => '',

        // Keep track of which items are popular and display those when opening Alfred sorted on most popular.
        'rememberPopularItems' => true,
        'maxPopularItemsOnInit' => 5,
    ],
];
