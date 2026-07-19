<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    public function scopeByCategory(Builder $query, string $slug): Builder
    {
        return $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
