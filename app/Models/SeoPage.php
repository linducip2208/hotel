<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoPage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'faq_json' => 'array',
        'meta_json' => 'array',
        'last_generated_at' => 'datetime',
        'regenerate_after' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
}
