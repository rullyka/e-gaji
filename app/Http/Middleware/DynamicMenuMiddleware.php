<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DynamicMenuMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            $userId = $user->id;

            // Cache menu berdasarkan user ID
            $cacheKey = "menu_user_{$userId}";

            // Force refresh menu jika ada parameter dalam request
            if ($request->has('refresh_menu')) {
                Cache::forget($cacheKey);
                Log::info('Menu cache cleared for user: ' . $userId);
            }

            $menuItems = Cache::remember($cacheKey, 60*24, function () use ($user) {
                Log::info('Generating menu for user: ' . $user->id);
                return $this->getMenuForUser($user);
            });

            // Set menu ke config AdminLTE
            config(['adminlte.menu' => $menuItems]);

            // Log the menu for debugging
            Log::info('Menu for user ' . $userId, ['menu' => $menuItems]);
        }

        return $next($request);
    }

    private function getMenuForUser($user)
    {
        // Ambil semua menu dari database
        $menus = Menu::with('children')
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();

        $formattedMenu = [];

        foreach ($menus as $menu) {
            // Untuk tipe header
            if ($menu->type === 'header') {
                // Check if at least one child menu is accessible
                $hasAccessibleChildren = false;

                // Get direct children of this header
                $childMenus = Menu::where('parent_id', $menu->id)
                    ->orderBy('order')
                    ->get();

                // Also consider menu items that come after this header
                $nextMenus = Menu::whereNull('parent_id')
                    ->where('order', '>', $menu->order)
                    ->where('type', '!=', 'header')
                    ->orderBy('order')
                    ->get();

                // Combine both collections
                $relevantMenus = $childMenus->merge($nextMenus);

                foreach ($relevantMenus as $childMenu) {
                    // Stop checking if we hit another header
                    if ($childMenu->type === 'header' && is_null($childMenu->parent_id)) {
                        break;
                    }

                    // Check if the user has permission to see this menu item
                    if (!$childMenu->permission || $user->can($childMenu->permission)) {
                        $hasAccessibleChildren = true;
                        break;
                    }
                }

                if ($hasAccessibleChildren) {
                    $formattedMenu[] = ['header' => $menu->text];
                }
                continue;
            }

            // Skip if user doesn't have permission for this menu item
            if ($menu->permission && !$user->can($menu->permission)) {
                continue;
            }

            // Format menu item
            $item = ['text' => $menu->text];

            if ($menu->icon) $item['icon'] = $menu->icon;
            if ($menu->route) $item['route'] = $menu->route;

            // Tambahkan submenu jika ada
            if ($menu->children->count() > 0) {
                $submenu = [];

                foreach ($menu->children as $child) {
                    // Skip if user doesn't have permission
                    if ($child->permission && !$user->can($child->permission)) {
                        continue;
                    }

                    $childItem = ['text' => $child->text];

                    if ($child->icon) $childItem['icon'] = $child->icon;
                    if ($child->route) $childItem['route'] = $child->route;

                    $submenu[] = $childItem;
                }

                // Tambahkan submenu hanya jika ada item yang bisa diakses
                if (count($submenu) > 0) {
                    $item['submenu'] = $submenu;
                    $formattedMenu[] = $item;
                }
            } else {
                $formattedMenu[] = $item;
            }
        }

        return $formattedMenu;
    }
}