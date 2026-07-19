<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingPeriod extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = ['locked_at' => 'datetime'];

    public function property() { return $this->belongsTo(Property::class); }
    public function lockedBy() { return $this->belongsTo(User::class, 'locked_by_user_id'); }
}
