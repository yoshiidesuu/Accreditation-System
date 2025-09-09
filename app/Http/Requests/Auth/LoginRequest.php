<?php

namespace App\Http\Requests\Auth;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->getCredentials();

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Log failed login attempt
            $activityLogger = app(ActivityLogger::class);
            $activityLogger->logFailedLogin([
                'attempted_email' => $this->input('email'),
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'failure_reason' => 'invalid_credentials'
            ]);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Get the authentication credentials from the request.
     *
     * @return array
     */
    protected function getCredentials(): array
    {
        $login = $this->input('email');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'employee_id';
        
        return [
            $field => $login,
            'password' => $this->input('password'),
        ];
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        // Log rate limiting event
        $activityLogger = app(ActivityLogger::class);
        $activityLogger->logFailedLogin([
            'attempted_email' => $this->input('email'),
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'failure_reason' => 'rate_limited',
            'attempts_count' => RateLimiter::attempts($this->throttleKey())
        ]);

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
