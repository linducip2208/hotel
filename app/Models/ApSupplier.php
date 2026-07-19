<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApSupplier extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'subject_pph23' => 'boolean',
        'pph23_rate' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function bills() { return $this->hasMany(ApBill::class, 'supplier_id'); }
}
