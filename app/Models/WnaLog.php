<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WnaLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'passport_expires_at' => 'date',
        'reported_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
}
