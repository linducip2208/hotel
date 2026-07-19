<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestPreferenceHistory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['confidence' => 'decimal:2'];

    public function property() { return $this->belongsTo(Property::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
}
