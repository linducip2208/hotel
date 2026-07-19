<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ERegistrationCard extends Model
{
    use HasFactory;

    protected $table = 'e_registration_cards';
    protected $guarded = ['id'];

    protected $casts = [
        'signed_at' => 'datetime',
        'is_verified' => 'boolean',
        'submitted_data' => 'array',
    ];

    public function property()           { return $this->belongsTo(Property::class); }
    public function reservation()        { return $this->belongsTo(Reservation::class); }
    public function guest()              { return $this->belongsTo(Guest::class); }
    public function verifiedByStaff()    { return $this->belongsTo(User::class, 'verified_by_staff_id'); }
}
