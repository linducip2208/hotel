<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerDocument extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function owner() { return $this->belongsTo(User::class, 'owner_user_id'); }
}
