<?php

namespace Startselect\Alfred\WorkflowSteps\Routing;

use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;
use Startselect\Alfred\Contracts\PermissionChecker;
use Startselect\Alfred\Preparations\Core\Item;
use Startselect\Alfred\Preparations\Core\ItemSet;
use Startselect\Alfred\Preparations\PreparationFactory;
use Startselect\Alfred\WorkflowSteps\AbstractWorkflowStep;

class BasicRoutes extends AbstractWorkflowStep
{
    protected const ACTION_METHOD_INDEX = 'index';
    protected const ACTION_METHOD_CREATE = 'create';

    protected const SINGULAR_ACTION_METHODS = [
        self::ACTION_METHOD_CREATE,
    ];

    public function register(): Item|array|null
    {
        /** @var PermissionChecker $permissionChecker */
        $permissionChecker = App::make(PermissionChecker::class);

        // Keep track of certain types of routes
        $routeItemsByActionMethod = [
            self::ACTION_METHOD_INDEX => [],
            self::ACTION_METHOD_CREATE => [],
        ];

        /** @var Route $route */
        foreach (Arr::get(RouteFacade::getRoutes()->getRoutesByMethod(), 'GET', []) as $route) {
            // Are we fetching routes for this action method?
            if (
                isset($routeItemsByActionMethod[$route->getActionMethod()])
                && $routeItem = $this->createRouteItem($route, $permissionChecker)
            ) {
                $routeItemsByActionMethod[$route->getActionMethod()][] = $routeItem;
            }
        }

        return $this->createRegisterItems($routeItemsByActionMethod) ?: null;
    }

    protected function createRouteItem(Route $route, PermissionChecker $permissionChecker): ?Item
    {
        // Parameters required? Then we can't do a simple redirect
        if (str_contains($route->uri(), '{')) {
            return null;
        }

        // Getting the controller might fail
        try {
            $singular = in_array($route->getActionMethod(), self::SINGULAR_ACTION_METHODS);

            return (new Item())
                ->trigger(PreparationFactory::redirect(url($route->uri())))
                ->when(
                    $route->getName(),
                    function (Item $item, string $routeName) use ($route, $permissionChecker, $singular) {
                        // E.g. products.index, product_bundles.index
                        $nameParts = explode('.', $route->getName());
                        unset($nameParts[array_key_last($nameParts)]);
                        $itemName = ucfirst(strtolower(str_replace(['-', '_'], ' ', implode(' ', $nameParts))));

                        $item
                            ->name($singular ? Str::singular($itemName) : $itemName)
                            ->when(
                                $this->findPermissionForRouteItem($route, $itemName, $permissionChecker),
                                function (Item $item, string $permission) {
                                    $item->requiresPermission($permission);
                                }
                            );
                    }
                )
                ->when(!$route->getName(), function (Item $item) use ($route, $permissionChecker, $singular) {
                    // E.g. App\Http\Controllers\ProductsController
                    $controllerName = class_basename($route->getController());
                    $nameParts = preg_split('/(?=[A-Z])/', $controllerName);
                    unset($nameParts[array_key_last($nameParts)]);
                    $itemName = ucfirst(strtolower(trim(implode(' ', $nameParts))));

                    $item
                        ->name($singular ? Str::singular($itemName) : $itemName)
                        ->when(
                            $this->findPermissionForRouteItem($route, $itemName, $permissionChecker),
                            function (Item $item, string $permission) {
                                $item->requiresPermission($permission);
                            }
                        );
                });
        } catch (\Throwable) {
            // Don't report
        }

        return null;
    }

    protected function findPermissionForRouteItem(Route $route, string $itemName, PermissionChecker $checker): mixed
    {
        $searchPermissions = array_unique(array_filter([
            $route->getName(),
            Str::snake(Str::plural($itemName)) . '.' . $route->getActionMethod(),
        ]));

        foreach ($searchPermissions as $searchPermission) {
            if ($permission = $checker->findPermission($searchPermission)) {
                return $permission;
            }
        }

        return null;
    }

    protected function createRegisterItems(array $routeItemsByActionMethod): array
    {
        $items = [];

        foreach ($routeItemsByActionMethod as $routeActionMethod => $routeItems) {
            // Did we gather route items for this action method?
            if (!$routeItems) {
                continue;
            }

            $title = match ($routeActionMethod) {
                self::ACTION_METHOD_INDEX => 'Index / overview of things',
                self::ACTION_METHOD_CREATE => 'Create something new',
            };

            $items[] = (new Item())
                ->name(ucfirst($routeActionMethod))
                ->info("{$title}.")
                ->icon('compass')
                ->prefix(match ($routeActionMethod) {
                    self::ACTION_METHOD_INDEX => 'index',
                    self::ACTION_METHOD_CREATE => 'new',
                })
                ->trigger(
                    (new ItemSet())
                        ->title($title)
                        ->placeholder('Filter by found routes..')
                        ->items($routeItems)
                );
        }

        return $items;
    }
}
