<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpaMembership extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'auto_renew' => 'boolean', 'price' => 'decimal:2'];

    public function property() { return $this->belongsTo(Property::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function usages() { return $this->hasMany(SpaMembershipUsage::class, 'membership_id'); }
}
