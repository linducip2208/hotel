<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_included' => 'boolean',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
