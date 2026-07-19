<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Reservation extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    public function toSearchableArray(): array
    {
        $this->loadMissing('primaryGuest');
        return [
            'id' => $this->id,
            'ref' => $this->ref,
            'check_in' => $this->check_in?->toDateString(),
            'check_out' => $this->check_out?->toDateString(),
            'status' => $this->status,
            'guest_name' => $this->primaryGuest?->full_name,
            'guest_email' => $this->primaryGuest?->email,
            'guest_phone' => $this->primaryGuest?->phone,
        ];
    }

    public function searchableAs(): string { return 'reservations_index'; }

    protected $guarded = ['id'];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'arrival_time' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'pre_checkin_complete' => 'boolean',
        'children_ages' => 'array',
        'total_room' => 'decimal:2',
        'total_addons' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'balance' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'cancellation_penalty' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function primaryGuest() { return $this->belongsTo(Guest::class, 'primary_guest_id'); }
    public function company() { return $this->belongsTo(Company::class); }
    public function travelAgent() { return $this->belongsTo(TravelAgent::class); }
    public function rooms() { return $this->hasMany(ReservationRoom::class); }
    public function addons() { return $this->hasMany(ReservationAddon::class); }
    public function folios() { return $this->hasMany(Folio::class); }
    public function tokens() { return $this->hasMany(BookingAccessToken::class); }
    public function spaAppointments() { return $this->hasMany(SpaAppointment::class); }
    public function messageThreads() { return $this->hasMany(MessageThread::class); }
    public function reviews()        { return $this->hasMany(Review::class); }
    public function wnaLogs()        { return $this->hasMany(WnaLog::class); }
    public function doorLockEvents() { return $this->hasMany(DoorLockEvent::class); }
    public function otaVirtualCard() { return $this->hasOne(OtaVirtualCard::class); }
    public function guestRequests()  { return $this->hasMany(GuestRequest::class); }
    public function carbonFootprint(){ return $this->hasOne(CarbonFootprint::class); }
    public function surveyResponses(){ return $this->hasMany(SurveyResponse::class); }
    public function referralRedemption() { return $this->hasOne(ReferralRedemption::class); }
    public function createdByUser()    { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function reservationPackages() { return $this->hasMany(\App\Models\ReservationPackage::class); }
    public function digitalRegistrations() { return $this->hasMany(\App\Models\DigitalRegistration::class); }
}
