<?php

use App\Http\Controllers\Admin\AppUserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SidebarMenuController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Partner\AppUserController as PartnerAppUserController;
use App\Http\Controllers\Partner\DashboardController as PartnerDashboardController;
use App\Http\Controllers\PartnerRegisterController;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('test', function () {
    Benchmark::dd(function () {
        (string) view('welcome');
    });
});

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'doLogin'])->name('login.post');
Route::get('/register', [PartnerRegisterController::class, 'register'])->name('register');
Route::post('/register', [PartnerRegisterController::class, 'doRegister'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Users Routes
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::middleware('access_type:1')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/add', [UserController::class, 'add'])->name('add');
        Route::post('/create', [UserController::class, 'doCreate'])->name('create');
        Route::get('/detail/{id}', [UserController::class, 'detail'])->name('detail');
        Route::get('/update/{id}', [UserController::class, 'update'])->name('update');
        Route::post('/update/{id}', [UserController::class, 'doUpdate'])->name('doUpdate');
        Route::delete('/delete/{id}', [UserController::class, 'delete'])->name('delete');
        Route::post('/reset-password/{id}', [UserController::class, 'resetPassword'])->name('resetPassword');
    });

    Route::middleware('access_type:1')->prefix('app-users')->name('app_users.')->group(function () {
        Route::get('/', [AppUserController::class, 'index'])->name('index');
        Route::get('/add', [AppUserController::class, 'add'])->name('add');
        Route::post('/create', [AppUserController::class, 'doCreate'])->name('create');
        Route::get('/detail/{id}', [AppUserController::class, 'detail'])->name('detail');
        Route::get('/update/{id}', [AppUserController::class, 'update'])->name('update');
        Route::post('/update/{id}', [AppUserController::class, 'doUpdate'])->name('doUpdate');
        Route::delete('/delete/{id}', [AppUserController::class, 'delete'])->name('delete');
        Route::post('/approve/{id}', [AppUserController::class, 'approve'])->name('approve');
        Route::post('/reject/{id}', [AppUserController::class, 'reject'])->name('reject');
    });

    Route::middleware('access_type:1')->prefix('sidebar-menu')->name('sidebar_menu.')->group(function () {
        Route::get('/', [SidebarMenuController::class, 'index'])->name('index');
        Route::get('/refresh-cache', [SidebarMenuController::class, 'refreshCache'])->name('refresh_cache');
        Route::get('/add', [SidebarMenuController::class, 'add'])->name('add');
        Route::post('/create', [SidebarMenuController::class, 'doCreate'])->name('create');
        Route::get('/update/{id}', [SidebarMenuController::class, 'update'])->name('update');
        Route::post('/update/{id}', [SidebarMenuController::class, 'doUpdate'])->name('doUpdate');
        Route::delete('/delete/{id}', [SidebarMenuController::class, 'delete'])->name('delete');
        Route::get('/{id}/access', [SidebarMenuController::class, 'access'])->name('access');
        Route::post('/{id}/access', [SidebarMenuController::class, 'doAccess'])->name('doAccess');
        Route::get('/role-access/{accessType}', [SidebarMenuController::class, 'roleAccess'])->name('role_access');
        Route::post('/role-access/{accessType}', [SidebarMenuController::class, 'doRoleAccess'])->name('doRoleAccess');
    });

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/change-password', [UserController::class, 'changePassword'])->name('change_password');
        Route::post('/change-password', [UserController::class, 'doChangePassword'])->name('do_change_password');
    });

});

// Partner Routes
Route::middleware(['auth', 'access_type:2'])->prefix('partner')->name('partner.')->group(function () {
    Route::get('/dashboard', [PartnerDashboardController::class, 'index'])->name('dashboard');

    Route::prefix('app-users')->name('app_users.')->group(function () {
        Route::get('/', [PartnerAppUserController::class, 'index'])->name('index');
        Route::get('/add', [PartnerAppUserController::class, 'add'])->name('add');
        Route::post('/create', [PartnerAppUserController::class, 'doCreate'])->name('create');
        Route::get('/detail/{id}', [PartnerAppUserController::class, 'detail'])->name('detail');
        Route::get('/update/{id}', [PartnerAppUserController::class, 'update'])->name('update');
        Route::post('/update/{id}', [PartnerAppUserController::class, 'doUpdate'])->name('doUpdate');
        Route::delete('/delete/{id}', [PartnerAppUserController::class, 'delete'])->name('delete');
        Route::post('/resubmit/{id}', [PartnerAppUserController::class, 'resubmit'])->name('resubmit');
    });
});
