<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodWasteLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'logged_date' => 'date',
        'quantity_kg' => 'decimal:3',
        'estimated_cost' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function outlet() { return $this->belongsTo(PosOutlet::class); }
    public function loggedBy() { return $this->belongsTo(User::class, 'logged_by_user_id'); }
}
