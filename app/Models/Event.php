<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'event_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'av_equipment' => 'array',
        'venue_rate' => 'decimal:2',
        'fnb_total' => 'decimal:2',
        'addons_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'deposit_paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function functionRoom() { return $this->belongsTo(FunctionRoom::class); }
    public function company() { return $this->belongsTo(Company::class); }
    public function primaryContact() { return $this->belongsTo(Guest::class, 'primary_contact_guest_id'); }
    public function menuItems() { return $this->hasMany(EventMenuItem::class); }
}
