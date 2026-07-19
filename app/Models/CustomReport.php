<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomReport extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'widgets' => 'array',
        'is_public' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
