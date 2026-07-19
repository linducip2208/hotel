<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'received_date' => 'date',
        'refunded_amount' => 'decimal:2',
        'refund_date' => 'date',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function folioCharge() { return $this->belongsTo(FolioCharge::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
