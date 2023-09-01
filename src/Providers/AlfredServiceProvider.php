<?php

namespace Startselect\Alfred\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\Contracts\PermissionChecker;
use Startselect\Alfred\WorkflowStepProvider;

class AlfredServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/alfred.php', 'alfred');

        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        $this->publishes([
            __DIR__ . '/../../dist' => public_path('vendor/alfred'),
        ], 'alfred-assets');
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(WorkflowStepProvider::class, function () {
            return new WorkflowStepProvider(
                Config::get('alfred.registerWorkflowSteps', []),
                Config::get('alfred.optionalWorkflowSteps', []),
            );
        });

        $this->app->singleton(Alfred::class, function () {
            $workflowStepProvider = $this->app->make(WorkflowStepProvider::class);

            return new Alfred($workflowStepProvider);
        });

        $this->app->singleton(PermissionChecker::class, function () {
            return new (Config::get('alfred.permissionChecker'));
        });
    }
}
