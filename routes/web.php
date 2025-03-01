<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\RoleAccessController;
use App\Http\Controllers\Admin\UserAccessController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


// Redirect root ke halaman login
Route::get('/', function () {
    return redirect('/login');
});

// Route group untuk admin panel
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    // Dashboard admin
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // User Management
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);

    // Configuration
    Route::resource('menu', MenuController::class);
    Route::post('menu/update-order', [MenuController::class, 'updateOrder'])
        ->name('menu.update-order');  // Tambahkan ini
    Route::get('role-access', [RoleAccessController::class, 'index'])->name('role-access.index');
    Route::post('role-access/{role}', [RoleAccessController::class, 'update'])->name('role-access.update');
    Route::post('role-access/{role}/copy', [RoleAccessController::class, 'copyPermissions'])
        ->name('role-access.copy-permissions'); // Tambahkan ini


    Route::get('user-access', [UserAccessController::class, 'index'])->name('user-access.index');
    Route::post('user-access/{user}', [UserAccessController::class, 'update'])->name('user-access.update');
    Route::post('user-access/{user}/copy', [UserAccessController::class, 'copyAccess'])
        ->name('user-access.copy-access');

    // Profile routes tetap ada tapi dalam grup admin
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('/debug-menu', function () {
    dd(config('adminlte.menu'));
});
require __DIR__.'/auth.php';
