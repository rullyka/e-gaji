<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\BagianController;
use App\Http\Controllers\Admin\LemburController;
use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Admin\JabatanController;
use App\Http\Controllers\Admin\ProfesiController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\HariliburController;
use App\Http\Controllers\Admin\DepartemenController;
use App\Http\Controllers\Admin\MastercutiController;
use App\Http\Controllers\Admin\PenggajianController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleAccessController;
use App\Http\Controllers\Admin\UangTungguController;
use App\Http\Controllers\Admin\UserAccessController;
use App\Http\Controllers\Admin\JadwalkerjaController;
use App\Http\Controllers\Admin\MastershiftController;
use App\Http\Controllers\Admin\PeriodeGajiController;
use App\Http\Controllers\Admin\CutiKaryawanController;
use App\Http\Controllers\Admin\MesinAbsensiController;
use App\Http\Controllers\Admin\ProgramStudiController;

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


Route::get('/karyawans/get-all-active', [KaryawanController::class, 'getAllActive'])
    ->name('karyawans.get-all-active');

Route::get('karyawans/search', [KaryawanController::class, 'search'])->name('karyawans.search');

// Register permission check middleware
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {

    Route::post('mesinabsensis/clone-users', [MesinAbsensiController::class, 'cloneUsers'])
        ->name('mesinabsensis.clone-users');

    Route::get('mesinabsensis/{mesinabsensi}/auto-detect-ip', [MesinAbsensiController::class, 'autoDetectIp'])
        ->name('mesinabsensis.auto-detect-ip');

    Route::post('mesinabsensis/{mesinabsensi}/upload-direct-batch', [MesinAbsensiController::class, 'uploadDirectBatch'])
        ->name('mesinabsensis.upload-direct-batch');
    // Dashboard admin - accessible to all authenticated users
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // User Management - requires specific permissions
    Route::middleware('permission.check:users.view')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
    });

    Route::middleware('permission.check:users.create')->group(function () {
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
    });

    Route::middleware('permission.check:users.edit')->group(function () {
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    });

    Route::middleware('permission.check:users.delete')->group(function () {
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Role Management
    Route::middleware('permission.check:roles.view')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    });

    Route::middleware('permission.check:roles.create')->group(function () {
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    });

    Route::middleware('permission.check:roles.edit')->group(function () {
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });

    Route::middleware('permission.check:roles.delete')->group(function () {
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Permission Management
    Route::middleware('permission.check:permissions.view')->group(function () {
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    });

    Route::get('permissions/update-db', [PermissionController::class, 'updatePermissions'])
        ->name('permissions.update-db');

    Route::middleware('permission.check:permissions.create')->group(function () {
        Route::get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
        Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
    });

    Route::middleware('permission.check:permissions.edit')->group(function () {
        Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    });

    Route::middleware('permission.check:permissions.delete')->group(function () {
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    });

    // Menu Management
    Route::middleware('permission.check:menu.view')->group(function () {
        Route::get('menu', [MenuController::class, 'index'])->name('menu.index');
    });

    Route::middleware('permission.check:menu.create')->group(function () {
        Route::get('menu/create', [MenuController::class, 'create'])->name('menu.create');
        Route::post('menu', [MenuController::class, 'store'])->name('menu.store');
    });

    Route::middleware('permission.check:menu.edit')->group(function () {
        Route::get('menu/{menu}/edit', [MenuController::class, 'edit'])->name('menu.edit');
        Route::put('menu/{menu}', [MenuController::class, 'update'])->name('menu.update');
        Route::post('menu/update-order', [MenuController::class, 'updateOrder'])->name('menu.update-order');
    });

    Route::middleware('permission.check:menu.delete')->group(function () {
        Route::delete('menu/{menu}', [MenuController::class, 'destroy'])->name('menu.destroy');
    });

    // Role Access Management
    Route::middleware('permission.check:roles.view')->group(function () {
        Route::get('role-access', [RoleAccessController::class, 'index'])->name('role-access.index');
    });

    Route::middleware('permission.check:roles.edit')->group(function () {
        Route::post('role-access/{role}', [RoleAccessController::class, 'update'])->name('role-access.update');
        Route::post('role-access/{role}/copy', [RoleAccessController::class, 'copyPermissions'])->name('role-access.copy-permissions');
    });

    // User Access Management
    Route::middleware('permission.check:users.view')->group(function () {
        Route::get('user-access', [UserAccessController::class, 'index'])->name('user-access.index');
    });

    Route::middleware('permission.check:users.edit')->group(function () {
        Route::post('user-access/{user}', [UserAccessController::class, 'update'])->name('user-access.update');
        Route::post('user-access/{user}/copy', [UserAccessController::class, 'copyAccess'])->name('user-access.copy-access');
    });

    // Profile routes available to all authenticated users
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('bagians', BagianController::class);
    Route::resource('departemens', DepartemenController::class);
    Route::resource('program_studis', ProgramStudiController::class);
    Route::resource('hariliburs', HariliburController::class);
    // Additional routes for generating Sundays
    Route::get('hariliburs-generate-sundays', [HariliburController::class, 'generateSundaysForm'])
        ->name('hariliburs.generate-sundays-form');
    Route::post('hariliburs-generate-sundays', [HariliburController::class, 'generateSundays'])
        ->name('hariliburs.generate-sundays');

    Route::resource('profesis', ProfesiController::class);
    Route::resource('jabatans', JabatanController::class);
    Route::resource('karyawans', KaryawanController::class);
    Route::get('karyawans/get-bagians/{id_departemen}', [KaryawanController::class, 'getBagiansByDepartemen'])
        ->name('karyawans.get-bagians');

    Route::get('/get-nik', [KaryawanController::class, 'getNik'])->name('karyawans.get-nik');

    Route::resource('mastercutis', MastercutiController::class);
    Route::resource('shifts', ShiftController::class);
    Route::resource('cuti_karyawans', CutiKaryawanController::class);

    Route::resource('jadwalkerjas', JadwalkerjaController::class);
    Route::get('jadwalkerjas/report', [JadwalkerjaController::class, 'report'])->name('jadwalkerjas.report');


    Route::get('lemburs/{lembur}/approval', [LemburController::class, 'approvalForm'])
        ->name('lemburs.approval');
    Route::post('lemburs/{lembur}/approve', [LemburController::class, 'approve'])
        ->name('lemburs.approve');
    Route::resource('lemburs', LemburController::class);

    Route::put('mesinabsensis/{mesinabsensi}/toggle-status', [MesinAbsensiController::class, 'toggleStatus'])
        ->name('mesinabsensis.toggle-status');
    Route::resource('mesinabsensis', MesinAbsensiController::class);
    Route::resource('uangtunggus', UangTungguController::class);
    Route::resource('absensis', AbsensiController::class);


    Route::put('mesinabsensis/{mesinabsensi}/toggle-status', [MesinAbsensiController::class, 'toggleStatus'])
        ->name('mesinabsensis.toggle-status');
    Route::resource('mesinabsensis', MesinAbsensiController::class);
    Route::resource('uangtunggus', UangTungguController::class);
    Route::resource('mesinabsensis', MesinAbsensiController::class);

    // Mesin Absensi - Test koneksi dan toggle status
    Route::get('mesinabsensis/{mesinabsensi}/test-connection', [MesinAbsensiController::class, 'testConnection'])
        ->name('mesinabsensis.test-connection');
    Route::put('mesinabsensis/{mesinabsensi}/toggle-status', [MesinAbsensiController::class, 'toggleStatus'])
        ->name('mesinabsensis.toggle-status');

    // Mesin Absensi - Download logs
    Route::get('mesinabsensis/{mesinabsensi}/download-logs', [MesinAbsensiController::class, 'downloadLogs'])
        ->name('mesinabsensis.download-logs');
    Route::get('mesinabsensis/download-logs-range', [MesinAbsensiController::class, 'downloadLogsRange'])
        ->name('mesinabsensis.download-logs-range');
    Route::get('mesinabsensis/download-logs-user', [MesinAbsensiController::class, 'downloadLogsUser'])
        ->name('mesinabsensis.download-logs-user');
    Route::post('mesinabsensis/{mesinabsensi}/process-logs', [MesinAbsensiController::class, 'processLogs'])
        ->name('mesinabsensis.process-logs');

    // Mesin Absensi - Upload names
    Route::get('mesinabsensis/{mesinabsensi}/upload-names', [MesinAbsensiController::class, 'showUploadNames'])
        ->name('mesinabsensis.upload-names');
    Route::post('mesinabsensis/{mesinabsensi}/upload-names', [MesinAbsensiController::class, 'uploadNames'])
        ->name('mesinabsensis.upload-names-store');
    Route::post('mesinabsensis/{mesinabsensi}/upload-names-batch', [MesinAbsensiController::class, 'uploadNamesBatch'])
        ->name('mesinabsensis.upload-names-batch');
    Route::post('mesinabsensis/sync-all-users', [MesinAbsensiController::class, 'syncAllUsers'])
        ->name('mesinabsensis.sync-all-users');

    // Route untuk mendapatkan daftar karyawan yang terdaftar di mesin
    Route::get('mesinabsensis/{mesinabsensi}/get-registered-users', [MesinAbsensiController::class, 'getRegisteredUsers'])
        ->name('mesinabsensis.get-registered-users');

    // Route::get('karyawans/get-all-active', [KaryawanController::class, 'getAllActive'])
    //     ->name('karyawans.get-all-active');

    // Route untuk menghapus karyawan dari mesin absensi
    Route::post('mesinabsensis/{mesinabsensi}/delete-user', [MesinAbsensiController::class, 'deleteUser'])
        ->name('mesinabsensis.delete-user');

    // Potongan routes
    Route::resource('potongans', \App\Http\Controllers\Admin\PotonganController::class);


    // PeriodeGaji routes
    Route::resource('periodegaji', \App\Http\Controllers\Admin\PeriodeGajiController::class);
    // Add these routes within your admin group
    Route::post('/periodegaji/generate-monthly', [PeriodeGajiController::class, 'generateMonthly'])->name('periodegaji.generate-monthly');
    Route::post('/periodegaji/generate-weekly', [PeriodeGajiController::class, 'generateWeekly'])->name('periodegaji.generate-weekly');
    Route::post('/periodegaji/delete-multiple', [PeriodeGajiController::class, 'deleteMultiple'])->name('periodegaji.delete-multiple');
    Route::put('/periodegaji/{periodegaji}/set-active', [PeriodeGajiController::class, 'setActive'])->name('periodegaji.set-active');


    Route::resource('penggajian', PenggajianController::class);

    // Additional routes for payroll functionality
    Route::post('penggajian/get-filtered-karyawans', [PenggajianController::class, 'getFilteredKaryawans'])->name('penggajian.getFilteredKaryawans');
    Route::post('penggajian/batch-process', [PenggajianController::class, 'batchProcess'])->name('penggajian.batchProcess');
    Route::get('penggajian/{penggajian}/payslip', [PenggajianController::class, 'generatePayslip'])->name('penggajian.payslip');
    Route::post('penggajian/{penggajian}/add-component', [PenggajianController::class, 'addComponent'])->name('penggajian.addComponent');
    Route::delete('penggajian/{penggajian}/remove-component', [PenggajianController::class, 'removeComponent'])->name('penggajian.removeComponent');

    // Report routes
    Route::get('penggajian-report/by-period', [PenggajianController::class, 'reportByPeriod'])->name('penggajian.reportByPeriod');
    Route::get('penggajian-report/by-department', [PenggajianController::class, 'reportByDepartment'])->name('penggajian.reportByDepartment');
    Route::get('penggajian-report/export-excel', [PenggajianController::class, 'exportExcel'])->name('penggajian.exportExcel');

    Route::post('penggajian/review', [PenggajianController::class, 'review'])->name('penggajian.review');
    Route::post('penggajian/process', [PenggajianController::class, 'process'])->name('penggajian.process');
});




require __DIR__ . '/auth.php';