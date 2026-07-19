<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'category_ratings' => 'array',
        'is_public' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
}
