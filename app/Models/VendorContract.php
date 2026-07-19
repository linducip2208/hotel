<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorContract extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'value' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function vendor() { return $this->belongsTo(Vendor::class); }
}
