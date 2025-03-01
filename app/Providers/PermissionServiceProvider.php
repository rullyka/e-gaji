<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Buat Blade directive @can_action yang mendeteksi permission dari route saat ini
        Blade::directive('can_action', function ($expression) {
            return "<?php if(\$this->canAccess($expression)): ?>";
});

Blade::directive('endcan_action', function () {
return "<?php endif; ?>";
});

// Buat method canAccess untuk blade engine
Blade::extend(function ($view) {
return "<?php
            if (!method_exists(\$this, 'canAccess')) {
                \$this->canAccess = function(\$action = null) {
                    if (!auth()->check()) return false;

                    // Super admin selalu bisa akses
                    if (auth()->user()->hasRole('super-admin')) return true;

                    // Jika action diberikan langsung, gunakan itu
                    if (\$action && auth()->user()->can(\$action)) return true;

                    // Auto-detect permission dari route
                    \$route = request()->route();
                    if (!\$route || !isset(\$route->action['controller'])) return false;

                    // Parse controller dan method
                    \$routeAction = \$route->action['controller'];
                    list(\$controller, \$method) = explode('@', \$routeAction);

                    // Auto-detect action jika tidak ada argument
                    if (empty(\$action)) {
                        // Format nama controller untuk permission
                        \$name = class_basename(\$controller);
                        \$name = str_replace('Controller', '', \$name);
                        \$kebabName = \\Illuminate\\Support\\Str::kebab(\$name);

                        // Tentukan module name
                        if (!\\Illuminate\\Support\\Str::contains(\$kebabName, '-')) {
                            \$module = \\Illuminate\\Support\\Str::plural(\$kebabName);
                        } else {
                            \$module = str_replace('-', '_', \$kebabName);
                        }

                        // Map method ke permission action
                        \$actionMap = [
                            'index' => 'view',
                            'show' => 'view',
                            'create' => 'create',
                            'store' => 'create',
                            'edit' => 'edit',
                            'update' => 'edit',
                            'destroy' => 'delete'
                        ];

                        foreach (\$actionMap as \$actionName => \$permissionName) {
                            if (\$method === \$actionName || \\Illuminate\\Support\\Str::startsWith(\$method, \$actionName)) {
                                \$permissionAction = \$permissionName;
                                break;
                            }
                        }

                        if (!isset(\$permissionAction)) {
                            \$permissionAction = \$method;
                        }

                        // Build permission name
                        \$permissionName = \$module . '.' . \$permissionAction;

                        return auth()->user()->can(\$permissionName);
                    } else {
                        // Jika parameter action diberikan, gunakan itu
                        return auth()->user()->can(\$action);
                    }
                };
            }
            ?>{$view}";
});
}
}