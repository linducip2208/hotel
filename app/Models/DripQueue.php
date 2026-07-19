<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DripQueue extends Model
{
    use HasFactory;

    protected $table = 'drip_queue';

    protected $guarded = ['id'];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function property()    { return $this->belongsTo(Property::class); }
    public function dripStep()    { return $this->belongsTo(DripStep::class); }
    public function guest()       { return $this->belongsTo(Guest::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
}
