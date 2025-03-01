<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

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
            }

            $menuItems = Cache::remember($cacheKey, 60*24, function () use ($user) {
                return $this->getMenuForUser($user);
            });

            // Set menu ke config AdminLTE
            config(['adminlte.menu' => $menuItems]);
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
                // Periksa apakah ada menu item di bawah header ini yang dapat diakses
                $hasAccessibleChildren = false;
                $childMenus = Menu::where(function($query) use ($menu) {
                    $query->where('parent_id', $menu->id)
                        ->orWhere(function($q) use ($menu) {
                            $q->whereNull('parent_id')
                                ->where('order', '>', $menu->order)
                                ->where('type', '!=', 'header');
                        });
                })->get();

                foreach ($childMenus as $childMenu) {
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

            // Skip jika user tidak memiliki permission
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
                    // Skip jika user tidak memiliki permission
                    if ($child->permission && !$user->can($child->permission)) {
                        continue;
                    }

                    $childItem = ['text' => $child->text];

                    if ($child->icon) $childItem['icon'] = $child->icon;
                    if ($child->route) $childItem['route'] = $child->route;

                    $submenu[] = $childItem;
                }

                // Tambahkan submenu hanya jika ada item
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
