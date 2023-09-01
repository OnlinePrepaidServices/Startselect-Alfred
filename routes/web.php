<?php

use Illuminate\Support\Facades\Route;
use Startselect\Alfred\Controllers\AlfredController;

Route::group(['middleware' => ['web', 'auth']], function () {
    Route::post('alfred/initiate', [AlfredController::class, 'initiate'])->name('alfred.initiate');
    Route::post('alfred/handle-workflow-step', [AlfredController::class, 'handleWorkflowStep'])->name('alfred.handle_workflow_step');
});
