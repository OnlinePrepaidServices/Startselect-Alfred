<?php

use Illuminate\Support\Facades\Route;
use Startselect\Alfred\Http\Controllers\AlfredInitiationController;
use Startselect\Alfred\Http\Controllers\AlfredItemSettingsController;
use Startselect\Alfred\Http\Controllers\AlfredWorkflowStepHandlerController;

Route::group(['middleware' => ['web', 'auth']], function () {
    Route::post('alfred/initiate', AlfredInitiationController::class)->name('alfred.initiate');
    Route::post('alfred/handle-workflow-step', AlfredWorkflowStepHandlerController::class)->name('alfred.handle_workflow_step');
    Route::post('alfred/save-item-settings', AlfredItemSettingsController::class)->name('alfred.save_item_settings');
});
