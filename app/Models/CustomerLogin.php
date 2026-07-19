<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CustomerLogin extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $table = 'guests';
    protected $fillable = ['first_name', 'last_name', 'email', 'phone', 'password', 'property_id'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'id_expires_at' => 'date',
            'is_vip' => 'boolean',
            'is_blacklisted' => 'boolean',
            'preferences' => 'array',
            'tags' => 'array',
            'marketing_consent' => 'boolean',
            'forgotten_at' => 'datetime',
        ];
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'primary_guest_id');
    }

    public function folios()
    {
        return $this->hasMany(Folio::class, 'guest_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
