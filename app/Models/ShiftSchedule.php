<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSchedule extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['date' => 'date'];

    public function property() { return $this->belongsTo(Property::class); }
    public function employee() { return $this->belongsTo(Employee::class); }
}
