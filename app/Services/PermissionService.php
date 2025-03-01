<?php

namespace App\Services;

use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    /**
     * Standard CRUD actions to their permission equivalents
     */
    protected $actionMap = [
        'index' => 'view',
        'show' => 'view',
        'create' => 'create',
        'store' => 'create',
        'edit' => 'edit',
        'update' => 'edit',
        'destroy' => 'delete',
        // Add additional mappings as needed
        'list' => 'view',
        'display' => 'view',
        'remove' => 'delete',
        'save' => 'create',
    ];

    /**
     * Methods that should be excluded from permission generation
     */
    protected $excludedMethods = [
        '__construct',
        'middleware',
        'authorize',
        'validateRequest',
        'callAction',
        'getMiddleware',
        'getValidationFactory',
        'validate',
        'validateWith',
        'dispatchNow',
    ];

    /**
     * Detect all permissions from controller
     */
    public function detectPermissions($controller)
    {
        $reflection = new ReflectionClass($controller);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        $controllerName = $this->getControllerName($controller);
        $permissions = [];

        foreach ($methods as $method) {
            // Skip methods from parent Controller class
            if ($method->class === 'App\\Http\\Controllers\\Controller') {
                continue;
            }

            // Skip methods from traits
            if ($method->class !== $controller) {
                continue;
            }

            if ($this->shouldGeneratePermission($method->getName())) {
                $permission = $this->generatePermissionFromMethod($controllerName, $method->getName());
                if ($permission && !in_array($permission, $permissions)) {
                    $permissions[] = $permission;
                }
            }
        }

        return $permissions;
    }

    /**
     * Generate permission name from method
     */
    protected function generatePermissionFromMethod($controller, $method)
    {
        // Check if method is in the action map
        foreach ($this->actionMap as $action => $permission) {
            if ($method === $action || Str::startsWith($method, $action)) {
                return $controller . '.' . $permission;
            }
        }

        // For custom methods that don't match standard patterns,
        // create a permission with the method name itself
        if (!Str::startsWith($method, ['get', 'set', '_'])) {
            return $controller . '.' . $method;
        }

        return null;
    }

    /**
     * Get controller name for permission
     */
    protected function getControllerName($controller)
    {
        $name = class_basename($controller);
        $name = str_replace('Controller', '', $name);

        // Convert to kebab case (e.g., UserAccess => user-access)
        // and then to snake case for the permission name (e.g., user-access => user_access)
        $kebab = Str::kebab($name);

        // Determine if we should pluralize based on controller name
        // For example, UserController becomes users, but UserAccessController becomes user_access
        if (!Str::contains($kebab, '-')) {
            // Simple controller name, pluralize it (e.g., user => users)
            return Str::plural($kebab);
        } else {
            // Complex name, don't pluralize (e.g., user-access stays as user_access)
            return str_replace('-', '_', $kebab);
        }
    }

    /**
     * Check if method needs permission generation
     */
    protected function shouldGeneratePermission($methodName)
    {
        return !in_array($methodName, $this->excludedMethods) &&
               !Str::startsWith($methodName, ['__', 'get', 'set', '_']);
    }

    /**
     * Sync permissions to database
     */
    public function syncPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}