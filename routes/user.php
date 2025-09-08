<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\CollegeController;
use App\Http\Controllers\User\AreaController;
use App\Http\Controllers\User\ParameterController;
use App\Http\Controllers\User\ParameterContentController;
use App\Http\Controllers\User\AccreditationController;
use App\Http\Controllers\User\SwotEntryController;
use App\Http\Controllers\User\ReportController;
use App\Http\Controllers\User\ProfileController;

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
|
| These routes are for non-admin users with role-based access control
|
*/

Route::middleware(['auth', 'role:coordinator,faculty,staff'])->prefix('user')->name('user.')->group(function () {
    // Dashboard - Available to all user roles
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile Management - Available to all user roles
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::put('/settings', [ProfileController::class, 'updateSettings'])->name('settings.update');
    
    // College Management - Coordinators and Faculty can view/edit their colleges
    Route::middleware('role:coordinator,faculty')->group(function () {
        Route::resource('colleges', CollegeController::class)->only(['index', 'show', 'edit', 'update']);
    });
    
    // Area Management - Coordinators can manage areas
    Route::middleware('role:coordinator')->group(function () {
        Route::resource('areas', AreaController::class);
    });
    
    // Parameter Management - Coordinators and Faculty can view/manage parameters
    Route::middleware('role:coordinator,faculty')->group(function () {
        Route::resource('parameters', ParameterController::class);
    });
    
    // Parameter Content Management - All user roles can manage content
    Route::resource('parameter-contents', ParameterContentController::class);
    Route::post('parameter-contents/{parameterContent}/submit', [ParameterContentController::class, 'submit'])->name('parameter-contents.submit');
    
    // Accreditation Management - Staff can manage accreditations
    Route::middleware('role:staff')->group(function () {
        Route::resource('accreditations', AccreditationController::class);
        Route::post('accreditations/{accreditation}/evaluate', [AccreditationController::class, 'evaluate'])->name('accreditations.evaluate');
        Route::post('accreditations/{accreditation}/submit-report', [AccreditationController::class, 'submitReport'])->name('accreditations.submit-report');
    });
    
    // SWOT Analysis - Coordinators and Faculty can manage SWOT entries
    Route::middleware('role:coordinator,faculty')->group(function () {
        Route::resource('swot-entries', SwotEntryController::class);
    });
    
    // Reports - Role-based report access
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        
        // Coordinators can view college reports
        Route::middleware('role:coordinator')->group(function () {
            Route::get('/colleges', [ReportController::class, 'colleges'])->name('colleges');
        });
        
        // Staff can view accreditation reports
        Route::middleware('role:staff')->group(function () {
            Route::get('/accreditations', [ReportController::class, 'accreditations'])->name('accreditations');
        });
        
        // Faculty can view their own reports
        Route::middleware('role:faculty')->group(function () {
            Route::get('/my-reports', [ReportController::class, 'myReports'])->name('my-reports');
        });
        
        // Export functionality based on role
        Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
    });
});