<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
}
