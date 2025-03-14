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
use App\Http\Controllers\Admin\KuotaCutiTahunanController;

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

//------------------------------------------------------------------
// Public Routes
//------------------------------------------------------------------

// Redirect root to login page
Route::get('/', function () {
    return redirect('/login');
});

// Global Karyawan routes
Route::get('/karyawans/get-all-active', [KaryawanController::class, 'getAllActive'])
    ->name('karyawans.get-all-active');
Route::get('karyawans/search', [KaryawanController::class, 'search'])
    ->name('karyawans.search');
Route::get('shifts/getNextCode', [ShiftController::class, 'getNextCode'])->name('shifts.getNextCode');

//------------------------------------------------------------------
// Protected Routes (Auth Required)
//------------------------------------------------------------------
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Profile Management
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    //------------------------------------------------------------------
    // User Management
    //------------------------------------------------------------------

    // Users
    Route::middleware('permission.check:users.view')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
    });
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->name('users.reset-password')
        ->middleware('auth');

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

    // Roles
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

    // Permissions
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

    // Menus
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

    //------------------------------------------------------------------
    // Organization Structure
    //------------------------------------------------------------------

    // Departemen
    Route::resource('departemens', DepartemenController::class);

    // Bagian
    Route::resource('bagians', BagianController::class);

    // Program Studi
    Route::resource('program_studis', ProgramStudiController::class);

    // Profesi
    Route::resource('profesis', ProfesiController::class);

    // Jabatan
    Route::resource('jabatans', JabatanController::class);

    //------------------------------------------------------------------
    // Employee Management
    //------------------------------------------------------------------

    // Karyawan
    Route::resource('karyawans', KaryawanController::class);
    Route::get('karyawans/get-bagians/{id_departemen}', [KaryawanController::class, 'getBagiansByDepartemen'])
        ->name('karyawans.get-bagians');
    Route::get('/get-nik', [KaryawanController::class, 'getNik'])->name('karyawans.get-nik');
    Route::patch('/karyawans/{karyawan}/resign', [KaryawanController::class, 'resign'])->name('karyawans.resign');
    //------------------------------------------------------------------
    // Time Management
    //------------------------------------------------------------------

    // Hari Libur
    Route::resource('hariliburs', HariliburController::class);
    Route::get('hariliburs-generate-sundays', [HariliburController::class, 'generateSundaysForm'])
        ->name('hariliburs.generate-sundays-form');
    Route::post('hariliburs-generate-sundays', [HariliburController::class, 'generateSundays'])
        ->name('hariliburs.generate-sundays');

    // Shifts
    Route::resource('shifts', ShiftController::class);


    // Jadwal Kerja
    Route::resource('jadwalkerjas', JadwalkerjaController::class);
    Route::get('jadwalkerjas/report', [JadwalkerjaController::class, 'report'])->name('jadwalkerjas.report');

    // Master Cuti
    Route::resource('mastercutis', MastercutiController::class);


    // Kuota Cuti Tahunan Routes
    Route::get('kuota-cuti', [KuotaCutiTahunanController::class, 'index'])->name('kuota-cuti.index');
    Route::get('kuota-cuti/report', [KuotaCutiTahunanController::class, 'report'])->name('kuota-cuti.report');
    Route::get('kuota-cuti/create', [KuotaCutiTahunanController::class, 'create'])->name('kuota-cuti.create');
    Route::post('kuota-cuti', [KuotaCutiTahunanController::class, 'store'])->name('kuota-cuti.store');
    Route::get('kuota-cuti/{id}/edit', [KuotaCutiTahunanController::class, 'edit'])->name('kuota-cuti.edit');
    Route::put('kuota-cuti/{id}', [KuotaCutiTahunanController::class, 'update'])->name('kuota-cuti.update');
    Route::delete('kuota-cuti/{id}', [KuotaCutiTahunanController::class, 'destroy'])->name('kuota-cuti.destroy');
    // Add this route with your other kuota-cuti routes
    Route::post('kuota-cuti/generate-massal', [KuotaCutiTahunanController::class, 'generateMassal'])->name('kuota-cuti.generate-massal');

    // Cuti Karyawan
    Route::resource('cuti_karyawans', CutiKaryawanController::class);
    // CutiKaryawan Routes
    Route::resource('cuti_karyawans', CutiKaryawanController::class);
    Route::get('cuti_karyawans/{cutiKaryawan}/approval', [CutiKaryawanController::class, 'approvalForm'])->name('cuti_karyawans.approval');
    Route::post('cuti_karyawans/{cutiKaryawan}/approve', [CutiKaryawanController::class, 'approve'])->name('cuti_karyawans.approve');

    // Lembur
    Route::resource('lemburs', LemburController::class);
    Route::get('lemburs/{lembur}/approval', [LemburController::class, 'approvalForm'])
        ->name('lemburs.approval');
    Route::post('lemburs/{lembur}/approve', [LemburController::class, 'approve'])
        ->name('lemburs.approve');

    //------------------------------------------------------------------
    // Attendance Management
    //------------------------------------------------------------------

    // Absensi
    Route::resource('absensis', AbsensiController::class);
    // Add this route with your other absensi routes
    Route::get('/absensis/check-schedule', [AbsensiController::class, 'checkSchedule'])->name('absensis.check-schedule');

    // Penambahan route untuk integrasi mesin absensi
    Route::get('/absensis/fetch', [AbsensiController::class, 'showFetchForm'])
        ->name('absensis.fetch.form');
    Route::post('/absensis/fetch', [AbsensiController::class, 'fetchData'])
        ->name('absensis.fetch.process');
    Route::get('/absensis/sync', [AbsensiController::class, 'startSync'])
        ->name('absensis.sync');
    Route::get('/absensis/report/daily', [AbsensiController::class, 'dailyReport'])
        ->name('absensis.report.daily');
    Route::get('/absensis/report/employee', [AbsensiController::class, 'employeeReport'])
        ->name('absensis.report.employee');

    // Mesin Absensi
    Route::resource('mesinabsensis', MesinAbsensiController::class);

    // Mesin Absensi - Basic Operations
    Route::put('mesinabsensis/{mesinabsensi}/toggle-status', [MesinAbsensiController::class, 'toggleStatus'])
        ->name('mesinabsensis.toggle-status');
    Route::get('mesinabsensis/{mesinabsensi}/test-connection', [MesinAbsensiController::class, 'testConnection'])
        ->name('mesinabsensis.test-connection');
    Route::get('mesinabsensis/{mesinabsensi}/auto-detect-ip', [MesinAbsensiController::class, 'autoDetectIp'])
        ->name('mesinabsensis.auto-detect-ip');

    // Mesin Absensi - User Management
    Route::get('mesinabsensis/{mesinabsensi}/get-registered-users', [MesinAbsensiController::class, 'getRegisteredUsers'])
        ->name('mesinabsensis.get-registered-users');
    Route::post('mesinabsensis/{mesinabsensi}/delete-user', [MesinAbsensiController::class, 'deleteUser'])
        ->name('mesinabsensis.delete-user');
    Route::post('mesinabsensis/clone-users', [MesinAbsensiController::class, 'cloneUsers'])
        ->name('mesinabsensis.clone-users');
    Route::post('mesinabsensis/sync-all-users', [MesinAbsensiController::class, 'syncAllUsers'])
        ->name('mesinabsensis.sync-all-users');

    // Mesin Absensi - Log Management
    Route::get('mesinabsensis/{mesinabsensi}/download-logs', [MesinAbsensiController::class, 'downloadLogs'])
        ->name('mesinabsensis.download-logs');
    Route::get('mesinabsensis/download-logs-range', [MesinAbsensiController::class, 'downloadLogsRange'])
        ->name('mesinabsensis.download-logs-range');
    Route::get('mesinabsensis/download-logs-user', [MesinAbsensiController::class, 'downloadLogsUser'])
        ->name('mesinabsensis.download-logs-user');
    Route::post('mesinabsensis/{mesinabsensi}/process-logs', [MesinAbsensiController::class, 'processLogs'])
        ->name('mesinabsensis.process-logs');
    Route::post('mesinabsensis/{mesinabsensi}/upload-direct-batch', [MesinAbsensiController::class, 'uploadDirectBatch'])
        ->name('mesinabsensis.upload-direct-batch');

    // Mesin Absensi - Name Management
    Route::get('mesinabsensis/{mesinabsensi}/upload-names', [MesinAbsensiController::class, 'showUploadNames'])
        ->name('mesinabsensis.upload-names');
    Route::post('mesinabsensis/{mesinabsensi}/upload-names', [MesinAbsensiController::class, 'uploadNames'])
        ->name('mesinabsensis.upload-names-store');
    Route::post('mesinabsensis/{mesinabsensi}/upload-names-batch', [MesinAbsensiController::class, 'uploadNamesBatch'])
        ->name('mesinabsensis.upload-names-batch');

    //------------------------------------------------------------------
    // Payroll Management
    //------------------------------------------------------------------

    // Uang Tunggu
    Route::resource('uangtunggus', UangTungguController::class);

    // Potongan
    Route::resource('potongans', \App\Http\Controllers\Admin\PotonganController::class);

    // Periode Gaji
    Route::resource('periodegaji', PeriodeGajiController::class);
    Route::post('/periodegaji/generate-monthly', [PeriodeGajiController::class, 'generateMonthly'])
        ->name('periodegaji.generate-monthly');
    Route::post('/periodegaji/generate-weekly', [PeriodeGajiController::class, 'generateWeekly'])
        ->name('periodegaji.generate-weekly');
    Route::post('/periodegaji/delete-multiple', [PeriodeGajiController::class, 'deleteMultiple'])
        ->name('periodegaji.delete-multiple');
    Route::put('/periodegaji/{periodegaji}/set-active', [PeriodeGajiController::class, 'setActive'])
        ->name('periodegaji.set-active');

    // Penggajian
    Route::resource('penggajian', PenggajianController::class);
    Route::post('penggajian/get-filtered-karyawans', [PenggajianController::class, 'getFilteredKaryawans'])
        ->name('penggajian.getFilteredKaryawans');
    Route::post('penggajian/batch-process', [PenggajianController::class, 'batchProcess'])
        ->name('penggajian.batchProcess');
    Route::get('penggajian/{penggajian}/payslip', [PenggajianController::class, 'generatePayslip'])
        ->name('penggajian.payslip');
    Route::post('penggajian/{penggajian}/add-component', [PenggajianController::class, 'addComponent'])
        ->name('penggajian.addComponent');
    Route::delete('penggajian/{penggajian}/remove-component', [PenggajianController::class, 'removeComponent'])
        ->name('penggajian.removeComponent');
    Route::post('penggajian/review', [PenggajianController::class, 'review'])
        ->name('penggajian.review');
    Route::post('penggajian/process', [PenggajianController::class, 'process'])
        ->name('penggajian.process');

    // Payroll Reports
    Route::get('penggajian-report/by-period', [PenggajianController::class, 'reportByPeriod'])
        ->name('penggajian.reportByPeriod');
    Route::get('penggajian-report/by-department', [PenggajianController::class, 'reportByDepartment'])
        ->name('penggajian.reportByDepartment');
    Route::get('penggajian-report/export-excel', [PenggajianController::class, 'exportExcel'])
        ->name('penggajian.exportExcel');

    // Payslip routes - add these to your admin group routes
    Route::get('/penggajian/periode/{periodeId}/payslips', [PenggajianController::class, 'generatePayslips'])
        ->name('penggajian.payslips');
    Route::get('/penggajian/{penggajian}/payslip', [PenggajianController::class, 'generatePayslip'])
        ->name('penggajian.payslip');
});

// Authentication Routes
require __DIR__ . '/auth.php';
