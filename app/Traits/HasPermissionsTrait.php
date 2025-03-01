<?php

namespace App\Traits;

use App\Services\PermissionService;

trait HasPermissionsTrait
{
    protected function registerPermissions()
    {
        $service = app(PermissionService::class);
        $permissions = $service->detectPermissions(get_class($this));
        $service->syncPermissions($permissions);

        $this->middleware(function ($request, $next) use ($permissions) {
            $action = $this->getActionName();
            $permission = $this->getPermissionForAction($action);

            if ($permission && !auth()->user()->can($permission)) {
                abort(403, 'Unauthorized action.');
            }

            return $next($request);
        });
    }

    protected function getActionName()
    {
        $action = debug_backtrace()[2]['function'];
        $permissionMap = [
            'index' => 'view',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'edit',
            'update' => 'edit',
            'destroy' => 'delete'
        ];

        return $permissionMap[$action] ?? $action;
    }

    protected function getPermissionForAction($action)
    {
        $controllerName = $this->getControllerName();
        return $controllerName . '.' . $action;
    }

    protected function getControllerName()
    {
        $name = class_basename($this);
        $name = str_replace('Controller', '', $name);
        return strtolower(str_plural($name));
    }
}