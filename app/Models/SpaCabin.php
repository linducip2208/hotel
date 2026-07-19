<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaCabin extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean'];

    public function property()     { return $this->belongsTo(Property::class); }
    public function appointments() { return $this->hasMany(SpaAppointment::class, 'cabin_id'); }
}
