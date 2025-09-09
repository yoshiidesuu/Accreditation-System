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

Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
    // Dashboard - Available to all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile Management - Available to all user roles
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::put('/settings', [ProfileController::class, 'updateSettings'])->name('settings.update');
    
    // Routes that require specific roles
    Route::middleware('role:coordinator,faculty,staff')->group(function () {
    
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
    
    // Coordinator Tagging - Overall coordinators can tag colleges and assign accreditors
    Route::middleware('role:overall_coordinator')->group(function () {
        Route::get('accreditations/coordinator-tagging', [AccreditationController::class, 'coordinatorTagging'])->name('accreditations.coordinator-tagging');
        Route::post('accreditations/{accreditation}/assign-accreditors', [AccreditationController::class, 'assignAccreditors'])->name('accreditations.assign-accreditors');
        Route::get('accreditations/{accreditation}/tagging', [AccreditationController::class, 'showTagging'])->name('accreditations.show-tagging');
        Route::post('accreditations/{accreditation}/tag-content', [AccreditationController::class, 'tagContent'])->name('accreditations.tag-content');
        Route::delete('accreditations/{accreditation}/untag-content', [AccreditationController::class, 'untagContent'])->name('accreditations.untag-content');
    });
    
    // Accreditor Access - Assigned accreditors can view dashboard and tagging interface
    Route::middleware('role:accreditor_lead,accreditor_member')->group(function () {
        Route::get('accreditations/accreditor-dashboard', [AccreditationController::class, 'accreditorDashboard'])->name('accreditations.accreditor-dashboard');
        Route::get('accreditations/{accreditation}/tagging', [AccreditationController::class, 'showTagging'])->name('accreditations.show-tagging-accreditor');
    });
    
    // Access Request routes
    Route::prefix('access-requests')->name('access-requests.')->group(function () {
        Route::get('/', [\App\Http\Controllers\User\AccessRequestController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\User\AccessRequestController::class, 'store'])->name('store');
        Route::get('/{accessRequest}', [\App\Http\Controllers\User\AccessRequestController::class, 'show'])->name('show');
        Route::post('/{accessRequest}/approve', [\App\Http\Controllers\User\AccessRequestController::class, 'approve'])->name('approve');
        Route::post('/{accessRequest}/reject', [\App\Http\Controllers\User\AccessRequestController::class, 'reject'])->name('reject');
    });
    
    // Share link access route
    Route::get('/share/{shareLink}', [\App\Http\Controllers\User\AccessRequestController::class, 'accessViaShareLink'])->name('share.access');
    
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
    }); // Close role-specific middleware group
});