<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'date_of_birth' => 'date',
        'joined_at' => 'date',
        'terminated_at' => 'date',
        'is_active' => 'boolean',
        'basic_salary' => 'decimal:2',
        'position_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'meal_allowance' => 'decimal:2',
        'other_allowances' => 'array',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function attendance() { return $this->hasMany(AttendanceLog::class); }
    public function payslips() { return $this->hasMany(Payslip::class); }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
