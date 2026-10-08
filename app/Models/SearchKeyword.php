<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchKeyword extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'last_searched_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public static function track(?int $propertyId, string $keyword, string $source = 'booking', int $resultsCount = 0): void
    {
        if (! $propertyId || trim($keyword) === '') {
            return;
        }

        $keyword = mb_substr(trim($keyword), 0, 190);

        $row = static::query()
            ->where('property_id', $propertyId)
            ->where('source', $source)
            ->where('keyword', $keyword)
            ->first();

        if ($row) {
            $row->increment('hits');
            $row->update([
                'results_count' => $resultsCount ?: $row->results_count,
                'last_searched_at' => now(),
            ]);

            return;
        }

        static::query()->create([
            'property_id' => $propertyId,
            'keyword' => $keyword,
            'source' => $source,
            'results_count' => $resultsCount,
            'hits' => 1,
            'locale' => app()->getLocale(),
            'last_searched_at' => now(),
        ]);
    }
}
