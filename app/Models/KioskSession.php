<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KioskSession extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'id_ocr_data' => 'array',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
}
