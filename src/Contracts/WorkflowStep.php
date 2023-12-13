<?php

namespace Startselect\Alfred\Contracts;

use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\Response;

interface WorkflowStep
{
    /**
     * Register workflow step.
     *
     * This will add one or more items to the opening response of Alfred.
     */
    public function register(): Item|array|null;

    /**
     * Initialize workflow step.
     *
     * This will return a response to be initialized, once called upon by Alfred.
     */
    public function init(): Response;

    /**
     * Handle triggered workflow step.
     *
     * This will return a response to be handled, once called upon by Alfred.
     */
    public function handle(): Response;
}
