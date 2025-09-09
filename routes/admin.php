<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\CollegeController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\ParameterController;
use App\Http\Controllers\Admin\ParameterContentController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AccreditationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\ReportController as MainReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\BrandingController;

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
    
    // Parameter Content Management
    Route::resource('parameter-contents', ParameterContentController::class);
    Route::post('parameter-contents/{parameterContent}/approve', [ParameterContentController::class, 'approve'])
        ->name('parameter-contents.approve');
    Route::post('parameter-contents/{parameterContent}/reject', [ParameterContentController::class, 'reject'])
        ->name('parameter-contents.reject');
    
    // Role Management
    Route::resource('roles', RoleController::class)->only(['index', 'edit', 'update']);
    Route::post('roles/bulk-update', [RoleController::class, 'bulkUpdate'])->name('roles.bulk-update');
    Route::get('roles/stats', [RoleController::class, 'stats'])->name('roles.stats');
    
    // Accreditations Management
    Route::resource('accreditations', AccreditationController::class)->except(['create', 'store']);
    Route::post('accreditations/bulk-update', [AccreditationController::class, 'bulkUpdate'])->name('accreditations.bulk-update');
    Route::get('accreditations/stats', [AccreditationController::class, 'stats'])->name('accreditations.stats');
    
    // Reports
    Route::resource('reports', ReportController::class)
        ->only(['index', 'show']);
    Route::get('reports/college/{college}', [ReportController::class, 'college'])
        ->name('reports.college');
    Route::get('reports/analytics', [ReportController::class, 'analytics'])
        ->name('reports.analytics');
    Route::post('reports/export', [ReportController::class, 'export'])
        ->name('reports.export');
    Route::post('reports/bulk-stats', [ReportController::class, 'bulkStats'])
        ->name('reports.bulk-stats');
    
    // New Report Exports (Rankings & SWOT)
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/', [MainReportController::class, 'index'])->name('index');
        Route::get('/stats', [MainReportController::class, 'getStats'])->name('stats');
        
        // Rankings Reports
        Route::get('/rankings/pdf', [MainReportController::class, 'rankingsPdf'])->name('rankings.pdf');
        Route::get('/rankings/csv', [MainReportController::class, 'rankingsCsv'])->name('rankings.csv');
        
        // SWOT Reports
        Route::get('/swot/pdf', [MainReportController::class, 'swotPdf'])->name('swot.pdf');
        Route::get('/swot/csv', [MainReportController::class, 'swotCsv'])->name('swot.csv');
    });

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('settings/maintenance', [SettingsController::class, 'toggleMaintenance'])->name('settings.maintenance');
    Route::post('settings/cache/clear', [SettingsController::class, 'clearCache'])->name('settings.cache.clear');
    Route::post('settings/optimize', [SettingsController::class, 'optimize'])->name('settings.optimize');

    // Theme Management
    Route::prefix('theme')->name('theme.')->group(function () {
        Route::get('/', [ThemeController::class, 'index'])->name('index');
        Route::put('/', [ThemeController::class, 'update'])->name('update');
        Route::post('/preview', [ThemeController::class, 'preview'])->name('preview');
        Route::get('/reset', [ThemeController::class, 'reset'])->name('reset');
        Route::post('/upload-logo', [ThemeController::class, 'uploadLogo'])->name('upload-logo');
        Route::post('/upload-favicon', [ThemeController::class, 'uploadFavicon'])->name('upload-favicon');
    });

    // Branding Management
    Route::prefix('branding')->name('branding.')->group(function () {
        Route::get('/', [BrandingController::class, 'index'])->name('index');
        Route::post('/upload', [BrandingController::class, 'upload'])->name('upload');
        Route::post('/{asset}/activate', [BrandingController::class, 'activate'])->name('activate');
        Route::delete('/{asset}', [BrandingController::class, 'delete'])->name('delete');
        Route::get('/{asset}/preview', [BrandingController::class, 'preview'])->name('preview');
    });

    // Audit Logs
    Route::prefix('audit')->name('audit.')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('/export', [AuditController::class, 'export'])->name('export');
        Route::get('/stats', [AuditController::class, 'stats'])->name('stats');
        Route::post('/cleanup', [AuditController::class, 'cleanup'])->name('cleanup');
        Route::get('/{activity}', [AuditController::class, 'show'])->name('show');
    });
});