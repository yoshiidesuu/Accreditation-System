<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorAuthController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
        $this->middleware('auth');
    }

    /**
     * Show the 2FA setup page
     */
    public function show(): View
    {
        $user = Auth::user();
        
        if (!$user->two_factor_enabled) {
            // Generate a new secret if not already set
            if (!$user->two_factor_secret) {
                $user->two_factor_secret = encrypt($this->google2fa->generateSecretKey());
                $user->save();
            }
            
            $qrCodeUrl = $this->getQRCodeUrl($user);
            $secret = decrypt($user->two_factor_secret);
            
            return view('auth.two-factor-setup', compact('qrCodeUrl', 'secret'));
        }
        
        return view('auth.two-factor-manage');
    }

    /**
     * Enable 2FA for the user
     */
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'password' => 'required|string'
        ]);

        $user = Auth::user();
        
        // Verify current password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'The provided password is incorrect.']);
        }

        $secret = decrypt($user->two_factor_secret);
        
        // Verify the 2FA code
        if (!$this->google2fa->verifyKey($secret, $request->code)) {
            return back()->withErrors(['code' => 'The provided two factor authentication code is invalid.']);
        }

        // Enable 2FA and generate recovery codes
        $user->two_factor_enabled = true;
        $user->two_factor_confirmed_at = now();
        $user->two_factor_recovery_codes = encrypt(json_encode($this->generateRecoveryCodes()));
        $user->save();

        return redirect()->route('two-factor.show')->with('status', 'Two factor authentication has been enabled.');
    }

    /**
     * Disable 2FA for the user
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = Auth::user();
        
        // Verify current password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'The provided password is incorrect.']);
        }

        // Disable 2FA
        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return redirect()->route('two-factor.show')->with('status', 'Two factor authentication has been disabled.');
    }

    /**
     * Show recovery codes
     */
    public function showRecoveryCodes(): View
    {
        $user = Auth::user();
        
        if (!$user->two_factor_enabled) {
            return redirect()->route('two-factor.show');
        }
        
        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        
        return view('auth.two-factor-recovery-codes', compact('recoveryCodes'));
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $user = Auth::user();
        
        // Verify current password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'The provided password is incorrect.']);
        }

        $user->two_factor_recovery_codes = encrypt(json_encode($this->generateRecoveryCodes()));
        $user->save();

        return redirect()->route('two-factor.recovery-codes')->with('status', 'Recovery codes have been regenerated.');
    }

    /**
     * Generate QR code URL
     */
    private function getQRCodeUrl($user): string
    {
        $secret = decrypt($user->two_factor_secret);
        $appName = config('app.name');
        $email = $user->email;
        
        return $this->google2fa->getQRCodeUrl(
            $appName,
            $email,
            $secret
        );
    }

    /**
     * Generate recovery codes
     */
    private function generateRecoveryCodes(): array
    {
        return Collection::times(8, function () {
            return Str::random(10) . '-' . Str::random(10);
        })->toArray();
    }
}
