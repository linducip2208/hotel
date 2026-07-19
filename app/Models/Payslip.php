<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'breakdown' => 'array',
        'paid_at' => 'datetime',
        'basic_salary' => 'decimal:2',
        'allowances_total' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'gross_total' => 'decimal:2',
        'bpjs_kesehatan_employee' => 'decimal:2',
        'bpjs_tk_employee' => 'decimal:2',
        'pph_21' => 'decimal:2',
        'deductions_total' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
}
