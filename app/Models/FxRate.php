<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FxRate extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'rate_date' => 'date',
        'rate' => 'decimal:8',
    ];

    public static function lookup(string $base, string $quote, ?\DateTimeInterface $on = null): ?float
    {
        $on ??= now();
        $rate = static::where('base_currency', $base)
            ->where('quote_currency', $quote)
            ->where('rate_date', '<=', $on->format('Y-m-d'))
            ->orderByDesc('rate_date')
            ->first();
        return $rate ? (float) $rate->rate : null;
    }
}
