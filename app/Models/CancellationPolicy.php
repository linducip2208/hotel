<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationPolicy extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'is_refundable' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'rules' => 'array',
    ];

    public function property()  { return $this->belongsTo(Property::class); }
    public function ratePlans() { return $this->hasMany(RatePlan::class); }

    /** Calculate penalty given days_before checkin date and total amount. */
    public function calculatePenalty(int $daysBefore, float $totalAmount): float
    {
        // Find smallest matching threshold (most strict rule) — sort asc, first match
        $rules = collect($this->rules ?? [])->sortBy('days_before');
        $rule = $rules->first(fn ($r) => $daysBefore <= ($r['days_before'] ?? 0));
        if (! $rule) return 0;
        $pct = (float) ($rule['penalty_pct'] ?? 0);
        return round($totalAmount * $pct / 100, 2);
    }
}
