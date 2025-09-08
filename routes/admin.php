<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CollegeController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\ParameterController;
use App\Http\Controllers\Admin\ParameterContentController;
use App\Http\Controllers\Admin\AccreditationController;
use App\Http\Controllers\Admin\SwotEntryController;
use App\Http\Controllers\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes are for admin users only and require 'admin' role
|
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::resource('users', UserController::class);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // College Management
    Route::resource('colleges', CollegeController::class);
    
    // Area Management
    Route::resource('areas', AreaController::class);
    
    // Parameter Management
    Route::resource('parameters', ParameterController::class);
    
    // Parameter Content Management
    Route::resource('parameter-contents', ParameterContentController::class);
    Route::post('parameter-contents/{parameterContent}/approve', [ParameterContentController::class, 'approve'])->name('parameter-contents.approve');
    Route::post('parameter-contents/{parameterContent}/reject', [ParameterContentController::class, 'reject'])->name('parameter-contents.reject');
    
    // Accreditation Management
    Route::resource('accreditations', AccreditationController::class);
    Route::post('accreditations/{accreditation}/assign-team', [AccreditationController::class, 'assignTeam'])->name('accreditations.assign-team');
    
    // SWOT Analysis Management
    Route::resource('swot-entries', SwotEntryController::class);
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/colleges', [ReportController::class, 'colleges'])->name('colleges');
        Route::get('/accreditations', [ReportController::class, 'accreditations'])->name('accreditations');
        Route::get('/users', [ReportController::class, 'users'])->name('users');
        Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
    });
    
    // System Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('index');
        Route::post('/update', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('update');
    });
});