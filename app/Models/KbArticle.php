<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class KbArticle extends Model
{
    use HasFactory, Searchable;

    protected $guarded = ['id'];
    protected $casts = [
        'tags'         => 'array',
        'is_published' => 'boolean',
        'is_public'    => 'boolean',
    ];

    public function toSearchableArray(): array
    {
        return [
            'id'       => $this->id,
            'title'    => $this->title,
            'content'  => $this->content,
            'category' => $this->category,
            'tags'     => $this->tags,
        ];
    }

    public function searchableAs(): string { return 'kb_articles_index'; }

    public function property() { return $this->belongsTo(Property::class); }
    public function author()   { return $this->belongsTo(User::class, 'author_user_id'); }
}
