<?php

use Startselect\Alfred\WorkflowSteps as CoreWorkflowSteps;

return [
    // List of workflow steps that will add one or more items to the opening response of Alfred
    'registerWorkflowSteps' => [
        CoreWorkflowSteps\Auth\Logout::class,
        CoreWorkflowSteps\Generic\FocusableFields::class,
        CoreWorkflowSteps\Routing\BasicRoutes::class,
        CoreWorkflowSteps\Routing\History::class,
        CoreWorkflowSteps\Snippets\CreateSnippet::class,
        CoreWorkflowSteps\Snippets\DeleteSnippet::class,
        CoreWorkflowSteps\Snippets\EditSnippet::class,
        CoreWorkflowSteps\Snippets\ExecuteSnippet::class,
    ],

    // List of workflow steps that can be processed, once called upon by Alfred
    'optionalWorkflowSteps' => [],

    // The authentication checker that'll be used when getting the authenticated user's settings
    'authenticationChecker' => Startselect\Alfred\Contracts\AuthenticationChecker::class,

    // The permission checker that'll be used when handling preparations and workflow steps required permissions
    'permissionChecker' => Startselect\Alfred\Support\DefaultPermissionChecker::class,

    // Alfred's initial tips
    'tips' => [],

    // Alfred's JavaScript settings
    'settings' => [
        // List of classes in the HTML, which contains inputs that can be focused.
        // Note: either needs to be inputs containing that class or a container with a label and form input.
        'focusableFieldsClasses' => [],

        // Keep track of which items are popular and display those when opening Alfred sorted on most popular.
        'rememberPopularItems' => true,
        'maxPopularItemsOnInit' => 5,

        // Alfred's default values
        'defaultValues' => [
            'placeholder' => 'Find actions..',
            'titleItemsEmpty' => 'No results',
            'titleItemsFallback' => 'Use [phrase] with..',
            'titleItemsPopular' => 'Recent searches',
            'titleItemsResults' => 'Results',
            'titleItemsUnfiltered' => 'Unfiltered results',
            'titleTips' => 'Narrow your search',
        ],

        // Alfred's timeouts in seconds
        'timeouts' => [
            'action' => 1.2,
            'messages' => 2.2,
        ],
    ],
];
