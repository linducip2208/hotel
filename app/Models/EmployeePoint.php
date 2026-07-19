<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePoint extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'earned_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function employee() { return $this->belongsTo(Employee::class); }
}
