<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class PermissionHelper
{
    /**
     * Check if the current user has access to the given action
     *
     * @param string|null $action
     * @return bool
     */
    public static function canAccess($action = null)
    {
        if (!auth()->check()) {
            return false;
        }

        // Super admin always has access
        if (auth()->user()->hasRole('super-admin')) {
            return true;
        }

        // If specific action is provided, use it
        if ($action && auth()->user()->can($action)) {
            return true;
        }

        // Auto-detect permission from route
        $route = request()->route();
        if (!$route || !isset($route->action['controller'])) {
            return false;
        }

        // Parse controller and method
        $routeAction = $route->action['controller'];
        list($controller, $method) = explode('@', $routeAction);

        // Auto-detect action if no argument is provided
        if (empty($action)) {
            // Format controller name for permission
            $name = class_basename($controller);
            $name = str_replace('Controller', '', $name);
            $kebabName = Str::kebab($name);

            // Determine module name
            if (!Str::contains($kebabName, '-')) {
                $module = Str::plural($kebabName);
            } else {
                $module = str_replace('-', '_', $kebabName);
            }

            // Map method to permission action
            $actionMap = [
                'index' => 'view',
                'show' => 'view',
                'create' => 'create',
                'store' => 'create',
                'edit' => 'edit',
                'update' => 'edit',
                'destroy' => 'delete'
            ];

            $permissionAction = $method;
            foreach ($actionMap as $actionName => $permissionName) {
                if ($method === $actionName || Str::startsWith($method, $actionName)) {
                    $permissionAction = $permissionName;
                    break;
                }
            }

            // Build permission name
            $permissionName = $module . '.' . $permissionAction;

            return auth()->user()->can($permissionName);
        } else {
            // If action parameter is provided, use it
            return auth()->user()->can($action);
        }
    }
}
