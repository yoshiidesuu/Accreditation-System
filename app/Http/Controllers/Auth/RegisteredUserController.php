<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    protected $activityLogger;

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255', 'no_xss'],
            'last_name' => ['required', 'string', 'max:255', 'no_xss'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'employee_id' => 'EMP' . str_pad(User::count() + 1, 4, '0', STR_PAD_LEFT),
            'first_name' => \App\Helpers\SecurityHelper::sanitizeInput($request->first_name),
            'last_name' => \App\Helpers\SecurityHelper::sanitizeInput($request->last_name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'staff', // Default role
            'status' => 'active',
            'permissions' => json_encode([]),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Log user registration
        $this->activityLogger->logLogin($user, [
            'login_method' => 'registration',
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'registration_data' => [
                'employee_id' => $user->employee_id,
                'role' => $user->role
            ]
        ]);

        $user = auth()->user();
        if ($user->hasRole('admin')) {
            return redirect(route('admin.dashboard', absolute: false));
        } else {
            return redirect(route('user.dashboard', absolute: false));
        }
    }
}
