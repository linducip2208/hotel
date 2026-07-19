<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyLicense extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    protected $table = 'property_licenses';

    public function property() { return $this->belongsTo(Property::class); }

    public function daysUntilExpiry(): int
    {
        if (!$this->expiry_date) return 99999;
        return (int) now()->startOfDay()->diffInDays($this->expiry_date, false);
    }

    public function expiryBadgeColor(): string
    {
        $days = $this->daysUntilExpiry();
        if ($days < 0) return 'red';
        if ($days <= 30) return 'amber';
        return 'green';
    }

    public function expiryLabel(): string
    {
        $days = $this->daysUntilExpiry();
        if ($days < 0) return abs($days) . ' hari lewat';
        if ($days === 0) return 'Hari ini';
        if ($days <= 30) return $days . ' hari';
        return $days . ' hari';
    }
}
