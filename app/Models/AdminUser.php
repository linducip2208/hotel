<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email', 'password', 'name', 'phone', 'role', 'permissions',
        'two_factor_secret_encrypted', 'two_factor_recovery_codes', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'two_factor_secret_encrypted', 'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'two_factor_recovery_codes' => 'encrypted:array',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
