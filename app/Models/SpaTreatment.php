<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaTreatment extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['inclusions' => 'array', 'is_active' => 'boolean', 'price' => 'decimal:2'];

    public function property()     { return $this->belongsTo(Property::class); }
    public function appointments() { return $this->hasMany(SpaAppointment::class, 'treatment_id'); }
}
