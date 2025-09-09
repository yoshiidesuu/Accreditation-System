<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'role',
        'status',
        'department',
        'position',
        'phone',
        'permissions',
        'last_login_at',
        'profile_photo',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'theme_mode',
        'theme_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'theme_preferences' => 'array',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getNameAttribute(): string
    {
        $name = trim($this->first_name . ' ' . $this->last_name);
        return $name ?: 'Unknown User';
    }

    /**
     * Get user's theme preference or system default
     */
    public function getThemeMode(): string
    {
        return $this->theme_mode ?? 'light';
    }

    /**
     * Set user's theme preference
     */
    public function setThemeMode(string $mode): void
    {
        $this->update(['theme_mode' => $mode]);
    }

    /**
     * Get user's theme preferences with defaults
     */
    public function getThemePreferences(): array
    {
        return array_merge([
            'primary_color' => '#800000',
            'sidebar_style' => 'default',
            'font_size' => 'medium',
        ], $this->theme_preferences ?? []);
    }

    /**
     * Update user's theme preferences
     */
    public function updateThemePreferences(array $preferences): void
    {
        $current = $this->theme_preferences ?? [];
        $updated = array_merge($current, $preferences);
        $this->update(['theme_preferences' => $updated]);
    }

    /**
     * Configure activity logging options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'email', 'role', 'status', 'department', 'position'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
