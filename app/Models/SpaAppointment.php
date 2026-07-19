<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaAppointment extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function treatment() { return $this->belongsTo(SpaTreatment::class, 'treatment_id'); }
    public function therapist() { return $this->belongsTo(SpaTherapist::class, 'therapist_id'); }
    public function cabin() { return $this->belongsTo(SpaCabin::class, 'cabin_id'); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function folio() { return $this->belongsTo(Folio::class); }
}
