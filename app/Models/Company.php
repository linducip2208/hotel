<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Company extends Model
{
    use HasFactory, Searchable;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active'        => 'boolean',
        'contract_start'   => 'date',
        'contract_end'     => 'date',
        'negotiated_rates' => 'array',
        'credit_limit'     => 'decimal:2',
    ];

    public function toSearchableArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'tax_id' => $this->tax_id,
            'email' => $this->email,
            'phone' => $this->phone,
            'city'  => $this->city,
        ];
    }

    public function searchableAs(): string { return 'companies_index'; }

    public function property()    { return $this->belongsTo(Property::class); }
    public function reservations(){ return $this->hasMany(Reservation::class); }
    public function arAccounts()  { return $this->hasMany(ArAccount::class); }
    public function events()      { return $this->hasMany(Event::class); }
    public function groupBlocks() { return $this->hasMany(GroupBlock::class); }
    public function folios()      { return $this->hasMany(Folio::class); }
    public function allotments()  { return $this->hasMany(Allotment::class); }
}
