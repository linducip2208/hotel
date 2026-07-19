<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KidsActivity extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'age_min' => 'integer',
        'age_max' => 'integer',
        'capacity' => 'integer',
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'schedule' => 'array',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function bookings() { return $this->hasMany(KidsBooking::class, 'kids_activity_id'); }

    public function getAgeRangeAttribute(): string
    {
        return $this->age_min . '–' . $this->age_max . ' tahun';
    }

    public function getAvailableSlotsAttribute(): int
    {
        $booked = $this->bookings()
            ->where('booking_date', today()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->count();
        return max(0, $this->capacity - $booked);
    }
}
