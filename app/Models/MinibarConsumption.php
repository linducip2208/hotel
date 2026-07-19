<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinibarConsumption extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'consumption_date' => 'date',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function product() { return $this->belongsTo(MinibarProduct::class, 'minibar_product_id'); }
    public function folioCharge() { return $this->belongsTo(FolioCharge::class); }
    public function chargedByUser() { return $this->belongsTo(User::class, 'charged_by_user_id'); }
}
