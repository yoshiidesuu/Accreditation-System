<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user && $user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } else {
        return redirect()->route('user.dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Two Factor Authentication Routes
    Route::get('/two-factor-authentication', [TwoFactorAuthController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor-authentication', [TwoFactorAuthController::class, 'enable'])->name('two-factor.enable');
    Route::delete('/two-factor-authentication', [TwoFactorAuthController::class, 'disable'])->name('two-factor.disable');
    Route::get('/two-factor-recovery-codes', [TwoFactorAuthController::class, 'showRecoveryCodes'])->name('two-factor.recovery-codes');
    Route::post('/two-factor-recovery-codes', [TwoFactorAuthController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery-codes.regenerate');
});

// Authentication Routes (Laravel Breeze)
require __DIR__.'/auth.php';

// Admin Routes
require __DIR__.'/admin.php';

// User Routes
require __DIR__.'/user.php';

// Google Drive Proxy Routes
Route::prefix('api/drive')->middleware(['auth'])->group(function () {
    Route::get('/proxy/{fileId}', [App\Http\Controllers\Api\DriveProxyController::class, 'proxy'])->name('storage.proxy');
    Route::post('/request-permission/{fileId}', [App\Http\Controllers\Api\DriveProxyController::class, 'requestPermission'])->name('drive.request-permission');
    Route::get('/info/{fileId}', [App\Http\Controllers\Api\DriveProxyController::class, 'info'])->name('drive.info');
    Route::post('/check-access', [App\Http\Controllers\Api\DriveProxyController::class, 'checkAccess'])->name('drive.check-access');
});
