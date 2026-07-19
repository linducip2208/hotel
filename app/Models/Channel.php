<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const API_FORMAT_BOOKING_COM   = 'booking_com';
    const API_FORMAT_AGODA        = 'agoda';
    const API_FORMAT_TRAVELOKA    = 'traveloka';
    const API_FORMAT_TIKET_COM    = 'tiket_com';
    const API_FORMAT_EXPEDIA_EQC  = 'expedia_eqc';
    const API_FORMAT_AIRBNB       = 'airbnb';
    const API_FORMAT_TRIP_COM     = 'trip_com';
    const API_FORMAT_PEGIPEGI     = 'pegipegi';
    const API_FORMAT_MISTER_ALADIN = 'mister_aladin';

    const API_FORMATS = [
        self::API_FORMAT_BOOKING_COM   => 'Booking.com',
        self::API_FORMAT_AGODA         => 'Agoda YCS',
        self::API_FORMAT_TRAVELOKA     => 'Traveloka TPI',
        self::API_FORMAT_TIKET_COM     => 'Tiket.com',
        self::API_FORMAT_EXPEDIA_EQC   => 'Expedia EQC',
        self::API_FORMAT_AIRBNB        => 'Airbnb',
        self::API_FORMAT_TRIP_COM      => 'Trip.com',
        self::API_FORMAT_PEGIPEGI      => 'Pegipegi',
        self::API_FORMAT_MISTER_ALADIN => 'Mister Aladin',
    ];

    protected $casts = [
        'credentials_encrypted' => 'encrypted:array',
        'config' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'two_way_sync' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    public function property()       { return $this->belongsTo(Property::class); }
    public function provider()       { return $this->belongsTo(Provider::class); }
    public function mappings()       { return $this->hasMany(ChannelRoomMapping::class); }
    public function syncLogs()       { return $this->hasMany(AriSyncLog::class); }
    public function conflicts()      { return $this->hasMany(ChannelConflict::class); }
    public function arAccount()      { return $this->hasOne(ArAccount::class); }
    public function virtualCards()   { return $this->hasMany(OtaVirtualCard::class); }
    public function rateOverrides()  { return $this->hasMany(RateOverride::class); }
    public function parityAlerts()   { return $this->hasMany(ChannelParityAlert::class); }
    public function pricingRules()   { return $this->hasMany(DynamicPricingRule::class); }

    public function getCredentials(): array
    {
        $channelCreds = $this->credentials_encrypted ?? [];
        $providerCreds = [];
        if ($this->provider) {
            $providerCreds['api_key'] = $this->provider->getApiKey();
            $providerCreds['secret'] = $this->provider->getSecret();
            $providerCreds['base_url'] = $this->provider->base_url;
            if ($this->provider->extra_config) {
                $providerCreds = array_merge($this->provider->extra_config, $providerCreds);
            }
        }
        return array_merge(array_filter($providerCreds, fn($v) => $v !== null), $channelCreds);
    }
}
