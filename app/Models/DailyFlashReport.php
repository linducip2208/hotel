<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyFlashReport extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'report_date' => 'date',
        'rooms_kpi' => 'array',
        'revenue_breakdown' => 'array',
        'tax_breakdown' => 'array',
        'payment_breakdown' => 'array',
        'source_mix' => 'array',
        'total_revenue' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
}
