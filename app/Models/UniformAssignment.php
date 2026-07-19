<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniformAssignment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'quantity_assigned' => 'integer',
        'assigned_date' => 'date',
        'returned_date' => 'date',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function linenCategory() { return $this->belongsTo(LinenCategory::class); }
}
