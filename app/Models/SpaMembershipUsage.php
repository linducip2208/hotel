<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaMembershipUsage extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['discount_amount' => 'decimal:2'];

    public function membership() { return $this->belongsTo(SpaMembership::class, 'membership_id'); }
    public function appointment() { return $this->belongsTo(SpaAppointment::class, 'spa_appointment_id'); }
}
