<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
}
