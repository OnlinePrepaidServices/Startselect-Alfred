<?php

namespace Startselect\Alfred\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Startselect\Alfred\Alfred;
use Startselect\Alfred\Contracts\AuthenticationChecker;
use Startselect\Alfred\Contracts\PermissionChecker;
use Startselect\Alfred\Contracts\PreferenceManager;
use Startselect\Alfred\WorkflowStepProvider;

class AlfredServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/alfred.php', 'alfred');

        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        $this->publishes([
            __DIR__ . '/../../dist' => public_path('vendor/alfred'),
        ], 'alfred-assets');
    }

    public function register(): void
    {
        $this->app->singleton(AuthenticationChecker::class, function () {
            return new (Config::get('alfred.authenticationChecker'));
        });

        $this->app->singleton(PermissionChecker::class, function () {
            return new (Config::get('alfred.permissionChecker'));
        });

        $this->app->singleton(PreferenceManager::class, function (Application $app) {
            return new (Config::get('alfred.preferenceManager'))($app->make(AuthenticationChecker::class));
        });

        $this->app->singleton(WorkflowStepProvider::class, function (Application $app) {
            return new WorkflowStepProvider(
                $app->make(PermissionChecker::class),
                $app->make(PreferenceManager::class),
                Config::get('alfred.registerWorkflowSteps', []),
                Config::get('alfred.optionalWorkflowSteps', []),
            );
        });

        $this->app->singleton(Alfred::class, function (Application $app) {
            $workflowStepProvider = $app->make(WorkflowStepProvider::class);

            return new Alfred($workflowStepProvider);
        });
    }
}
