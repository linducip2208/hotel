<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupBlock extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'cutoff_date' => 'date',
        'negotiated_rate' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function company() { return $this->belongsTo(Company::class); }
    public function masterFolio() { return $this->belongsTo(Folio::class, 'master_folio_id'); }
    public function rooms() { return $this->hasMany(GroupBlockRoom::class); }
}
