<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargebackEvidence extends Model
{
    use HasFactory;

    protected $table = 'chargeback_evidence';
    protected $guarded = ['id'];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function chargeback() { return $this->belongsTo(Chargeback::class); }
}
