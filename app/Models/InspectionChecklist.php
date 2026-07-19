<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionChecklist extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'inspected_at' => 'datetime',
        'items' => 'array',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function inspector() { return $this->belongsTo(User::class, 'inspector_id'); }
}
