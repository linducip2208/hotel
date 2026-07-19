<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryTransaction extends Model
{
    use HasFactory;

    protected $table = 'laundry_transactions';

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function linenCategory() { return $this->belongsTo(LinenCategory::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function performedBy() { return $this->belongsTo(User::class, 'performed_by_user_id'); }
}
