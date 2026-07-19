<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Guest extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $guarded = ['id'];

    protected $casts = [
        'date_of_birth' => 'date',
        'id_expires_at' => 'date',
        'is_vip' => 'boolean',
        'is_blacklisted' => 'boolean',
        'preferences' => 'array',
        'tags' => 'array',
        'marketing_consent' => 'boolean',
        'forgotten_at' => 'datetime',
    ];

    protected $hidden = ['id_number', 'id_photo_path'];

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country' => $this->country,
        ];
    }

    public function searchableAs(): string { return 'guests_index'; }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function property()       { return $this->belongsTo(Property::class); }
    public function reservations()   { return $this->hasMany(Reservation::class, 'primary_guest_id'); }
    public function folios()         { return $this->hasMany(Folio::class); }
    public function reviews()        { return $this->hasMany(Review::class); }
    public function loyaltyMember()  { return $this->hasOne(LoyaltyMember::class); }
    public function waitlistEntries(){ return $this->hasMany(WaitlistEntry::class); }
    public function messageThreads() { return $this->hasMany(MessageThread::class); }
    public function spaAppointments(){ return $this->hasMany(SpaAppointment::class); }
    public function lostAndFoundClaims() { return $this->hasMany(LostAndFound::class, 'claimed_by_guest_id'); }
    public function arAccounts()     { return $this->hasMany(ArAccount::class); }
    public function wnaLogs()        { return $this->hasMany(WnaLog::class); }
    public function giftVouchersIssued() { return $this->hasMany(GiftVoucher::class, 'issued_to_guest_id'); }
    public function doorLockEvents() { return $this->hasMany(DoorLockEvent::class); }
    public function guestRequests()  { return $this->hasMany(GuestRequest::class); }
    public function surveyResponses(){ return $this->hasMany(SurveyResponse::class); }
    public function referralCodes()  { return $this->hasMany(ReferralCode::class, 'owner_guest_id'); }
    public function profile()        { return $this->hasOne(GuestProfile::class); }
    public function promoUsages()    { return $this->hasMany(PromoCodeUsage::class); }
    public function notificationLogs() { return $this->morphMany(NotificationLog::class, 'notifiable'); }
    public function digitalRegistrations() { return $this->hasMany(\App\Models\DigitalRegistration::class); }
}
