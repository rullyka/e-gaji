<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\PermissionHelper;

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
        // Create Blade directive @can_action that uses our static helper
        Blade::directive('can_action', function ($expression) {
            return "<?php if(\\App\\Helpers\\PermissionHelper::canAccess($expression)): ?>";
});

Blade::directive('endcan_action', function () {
return "<?php endif; ?>";
});
}
}
