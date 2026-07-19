<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalRegistration extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'signed_at' => 'datetime',
        'form_data' => 'json',
    ];

    public function property()    { return $this->belongsTo(Property::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function guest()       { return $this->belongsTo(Guest::class); }
}
