<?php

namespace Startselect\Alfred\Contracts;

use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\Core\Response;

interface WorkflowStep
{
    /**
     * Register workflow step.
     *
     * Add items to the opening response of Alfred.
     */
    public function register(ItemSet $itemSet): void;

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
