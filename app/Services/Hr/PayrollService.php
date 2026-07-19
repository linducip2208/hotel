<?php

namespace App\Services\Hr;

use App\Models\Employee;
use App\Models\Payslip;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Generate payslip untuk employee dalam periode bulanan.
     * Calculate: gross, BPJS, PPh 21 (PTKP per status), net.
     *
     * Simplifikasi PPh 21:
     *  - PTKP TK/0: 54jt/year (4.5jt/mo); +4.5jt per dependent (max K/3)
     *  - Tarif progresif: 5% s.d 60jt; 15% 60-250jt; 25% 250-500jt; 30% 500jt-5mlyr; 35% > 5mlyr (annualized)
     */
    public function generatePayslip(Employee $emp, int $year, int $month, float $serviceCharge = 0, float $overtimeHours = 0): Payslip
    {
        return DB::transaction(function () use ($emp, $year, $month, $serviceCharge, $overtimeHours) {
            $allowances = (float) $emp->position_allowance + $emp->transport_allowance + $emp->meal_allowance;
            $allowances += collect((array) $emp->other_allowances)->sum();
            $overtimePay = $overtimeHours * ((float) $emp->basic_salary / 173); // simplified: 173 = 8h × 21.6 days

            $gross = (float) $emp->basic_salary + $allowances + $overtimePay + $serviceCharge;

            // BPJS Kesehatan: 1% employee (capped at 1% of 12jt)
            $bpjsKes = round(min($gross, 12000000) * 0.01, 2);
            // BPJS TK (JHT 2% + JP 1%): 3% employee
            $bpjsTk = round($gross * 0.03, 2);

            // PPh 21 simplified: monthly base × 12, deduct PTKP, apply progressive, divide by 12
            $annualGross = $gross * 12 - ($bpjsKes + $bpjsTk) * 12;
            $ptkp = $this->ptkp($emp);
            $taxable = max(0, $annualGross - $ptkp);
            $annualTax = $this->calculatePphProgressive($taxable);
            $monthlyPph = round($annualTax / 12, 2);

            $deductions = $bpjsKes + $bpjsTk + $monthlyPph;
            $net = $gross - $deductions;

            return Payslip::updateOrCreate(
                ['employee_id' => $emp->id, 'year' => $year, 'month' => $month],
                [
                    'basic_salary' => $emp->basic_salary,
                    'allowances_total' => $allowances,
                    'overtime_pay' => $overtimePay,
                    'service_charge' => $serviceCharge,
                    'gross_total' => $gross,
                    'bpjs_kesehatan_employee' => $bpjsKes,
                    'bpjs_tk_employee' => $bpjsTk,
                    'pph_21' => $monthlyPph,
                    'deductions_total' => $deductions,
                    'net_salary' => $net,
                    'breakdown' => [
                        'overtime_hours' => $overtimeHours,
                        'ptkp' => $ptkp,
                        'taxable_annual' => $taxable,
                        'annual_tax' => $annualTax,
                    ],
                    'status' => 'draft',
                ]
            );
        });
    }

    protected function ptkp(Employee $emp): float
    {
        $base = 54000000; // TK/0
        if (in_array($emp->marital_status, ['married', 'kawin'])) $base += 4500000;
        $base += min(3, $emp->dependents_count) * 4500000;
        return $base;
    }

    protected function calculatePphProgressive(float $taxable): float
    {
        $brackets = [
            [60000000, 0.05],
            [190000000, 0.15], // 60jt → 250jt
            [250000000, 0.25], // 250jt → 500jt
            [4500000000, 0.30], // 500jt → 5mlyr
            [PHP_INT_MAX, 0.35],
        ];
        $tax = 0;
        $rem = $taxable;
        foreach ($brackets as [$band, $rate]) {
            $portion = min($rem, $band);
            $tax += $portion * $rate;
            $rem -= $portion;
            if ($rem <= 0) break;
        }
        return round($tax, 2);
    }
}
