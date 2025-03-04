<?php

use Illuminate\Support\Facades\Route;
use Startselect\Alfred\Controllers\AlfredInitiationController;
use Startselect\Alfred\Controllers\AlfredWorkflowStepHandlerController;

Route::group(['middleware' => ['web', 'auth']], function () {
    Route::post('alfred/initiate', AlfredInitiationController::class)->name('alfred.initiate');
    Route::post('alfred/handle-workflow-step', AlfredWorkflowStepHandlerController::class)->name('alfred.handle_workflow_step');
});
