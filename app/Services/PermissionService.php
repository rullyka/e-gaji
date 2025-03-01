<?php

namespace App\Services;

use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    /**
     * Deteksi semua permission dari controller
     */
    public function detectPermissions($controller)
    {
        $reflection = new ReflectionClass($controller);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        $controllerName = $this->getControllerName($controller);
        $permissions = [];

        foreach ($methods as $method) {
            if ($this->shouldGeneratePermission($method->getName())) {
                $permission = $this->generatePermissionFromMethod($controllerName, $method->getName());
                if ($permission) {
                    $permissions[] = $permission;
                }
            }
        }

        return $permissions;
    }

    /**
     * Generate nama permission dari method
     */
    protected function generatePermissionFromMethod($controller, $method)
    {
        $permissionMap = [
            'index' => 'view',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'edit',
            'update' => 'edit',
            'destroy' => 'delete'
        ];

        if (isset($permissionMap[$method])) {
            return $controller . '.' . $permissionMap[$method];
        }

        return null;
    }

    /**
     * Get controller name untuk permission
     */
    protected function getControllerName($controller)
    {
        $name = class_basename($controller);
        $name = str_replace('Controller', '', $name);
        return Str::plural(Str::kebab($name));
    }

    /**
     * Check apakah method perlu generate permission
     */
    protected function shouldGeneratePermission($methodName)
    {
        $excludedMethods = [
            '__construct',
            'middleware',
            'authorize',
            'validateRequest',
            'callAction'
        ];

        return !in_array($methodName, $excludedMethods) &&
               !Str::startsWith($methodName, ['get', 'set', '_']);
    }

    /**
     * Sync permissions ke database
     */
    public function syncPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}