<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'posted_at' => 'date',
        'voided_at' => 'datetime',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function property()      { return $this->belongsTo(Property::class); }
    public function lines()         { return $this->hasMany(JournalLine::class); }
    public function source()        { return $this->morphTo(); }
    public function createdByUser() { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function postedByUser()  { return $this->belongsTo(User::class, 'posted_by_user_id'); }
    public function voidedByUser()  { return $this->belongsTo(User::class, 'voided_by_user_id'); }
}
