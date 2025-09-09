<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\CollegeController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\ParameterController;

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
    
    // Academic Year Management
    Route::resource('academic-years', AcademicYearController::class);
    Route::patch('academic-years/{academicYear}/toggle-active', [AcademicYearController::class, 'toggleActive'])
        ->name('academic-years.toggle-active');
    
    // Area Management
    Route::resource('areas', AreaController::class);
    
    // Get areas by college and academic year (AJAX)
    Route::get('areas/get-by-college-year', [AreaController::class, 'getByCollegeYear'])
        ->name('areas.get-by-college-year');

    // Parameter Management
    Route::resource('parameters', ParameterController::class);
    
    // AJAX routes for parameters
    Route::get('/parameters/by-area', [ParameterController::class, 'getParametersByArea'])
        ->name('parameters.by-area');
    Route::post('/parameters/reorder', [ParameterController::class, 'reorder'])
        ->name('parameters.reorder');
    
    // Audit Logs
    Route::prefix('audit')->name('audit.')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('/export', [AuditController::class, 'export'])->name('export');
        Route::get('/stats', [AuditController::class, 'stats'])->name('stats');
        Route::post('/cleanup', [AuditController::class, 'cleanup'])->name('cleanup');
        Route::get('/{activity}', [AuditController::class, 'show'])->name('show');
    });
});