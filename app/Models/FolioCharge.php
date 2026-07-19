<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FolioCharge extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'charge_date' => 'date',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_void' => 'boolean',
    ];

    public function folio()    { return $this->belongsTo(Folio::class); }
    public function property() { return $this->belongsTo(Property::class); }
    public function postedBy() { return $this->belongsTo(User::class, 'posted_by_user_id'); }
}
