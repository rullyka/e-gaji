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
        $lastHeaderIndex = -1;

        foreach ($menus as $menu) {
            // Untuk tipe header
            if ($menu->type === 'header') {
                // Simpan index header untuk pengecekan nanti
                $lastHeaderIndex = count($formattedMenu);
                $formattedMenu[] = ['header' => $menu->text, '_is_header' => true];
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

        // Bersihkan header yang tidak memiliki menu item setelahnya
        $cleanedMenu = [];
        $skipNext = false;

        for ($i = 0; $i < count($formattedMenu); $i++) {
            $item = $formattedMenu[$i];

            // Jika ini adalah header
            if (isset($item['_is_header']) && $item['_is_header']) {
                // Periksa apakah ini adalah item terakhir atau item berikutnya juga header
                if ($i == count($formattedMenu) - 1 ||
                    (isset($formattedMenu[$i+1]['_is_header']) && $formattedMenu[$i+1]['_is_header'])) {
                    // Skip header ini
                    continue;
                }

                // Header ini memiliki item setelahnya, simpan dan hapus flag _is_header
                unset($item['_is_header']);
                $cleanedMenu[] = $item;
            } else {
                $cleanedMenu[] = $item;
            }
        }

        return $cleanedMenu;
    }
}
