<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Property;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        Property::query()->each(fn (Property $p) => $this->seedFor($p));
    }

    public function seedFor(Property $property): void
    {
        $accounts = [
            ['1', 'Aset', 'header', 'debit'],
            ['1-1', 'Aset Lancar', 'header', 'debit'],
            ['1-1010', 'Kas', 'asset', 'debit'],
            ['1-1020', 'Bank — Operasional', 'asset', 'debit'],
            ['1-1100', 'Piutang Tamu (City Ledger)', 'asset', 'debit'],
            ['1-1110', 'Piutang OTA', 'asset', 'debit'],
            ['1-1200', 'Persediaan F&B', 'asset', 'debit'],
            ['1-1210', 'Persediaan Minibar', 'asset', 'debit'],
            ['1-1300', 'PPN Masukan', 'asset', 'debit'],
            ['2', 'Liabilitas', 'header', 'credit'],
            ['2-1', 'Liabilitas Lancar', 'header', 'credit'],
            ['2-1010', 'Hutang Usaha', 'liability', 'credit'],
            ['2-1100', 'PB1 Terhutang', 'liability', 'credit'],
            ['2-1110', 'PPN Keluaran', 'liability', 'credit'],
            ['2-1120', 'PPh 21 Terhutang', 'liability', 'credit'],
            ['2-1130', 'PPh 23 Terhutang', 'liability', 'credit'],
            ['2-1200', 'Service Charge Terhutang', 'liability', 'credit'],
            ['2-1300', 'Deposit Tamu', 'liability', 'credit'],
            ['2-1400', 'Pendapatan Diterima Dimuka', 'liability', 'credit'],
            ['3', 'Ekuitas', 'header', 'credit'],
            ['3-1010', 'Modal Disetor', 'equity', 'credit'],
            ['3-1020', 'Laba Ditahan', 'equity', 'credit'],
            ['4', 'Pendapatan', 'header', 'credit'],
            ['4-1010', 'Pendapatan Kamar', 'revenue', 'credit'],
            ['4-1020', 'Pendapatan F&B', 'revenue', 'credit'],
            ['4-1030', 'Pendapatan Minibar', 'revenue', 'credit'],
            ['4-1040', 'Pendapatan Laundry', 'revenue', 'credit'],
            ['4-1070', 'Pendapatan Lain', 'revenue', 'credit'],
            ['4-2000', 'Service Charge', 'revenue', 'credit'],
            ['5', 'HPP', 'header', 'debit'],
            ['5-1010', 'HPP F&B', 'expense', 'debit'],
            ['5-1020', 'HPP Minibar', 'expense', 'debit'],
            ['6', 'Beban Operasional', 'header', 'debit'],
            ['6-1010', 'Gaji & Upah', 'expense', 'debit'],
            ['6-1020', 'Listrik', 'expense', 'debit'],
            ['6-1030', 'Air', 'expense', 'debit'],
            ['6-1040', 'Internet & Telp', 'expense', 'debit'],
            ['6-1050', 'Komisi OTA', 'expense', 'debit'],
            ['6-1060', 'Biaya Channel Manager', 'expense', 'debit'],
            ['6-1070', 'Biaya Payment Gateway', 'expense', 'debit'],
            ['6-1080', 'Pemeliharaan & Perbaikan', 'expense', 'debit'],
            ['6-1090', 'Marketing & Iklan', 'expense', 'debit'],
            ['6-1100', 'Perlengkapan Tamu', 'expense', 'debit'],
            ['6-1110', 'Laundry Supplies', 'expense', 'debit'],
            ['7', 'Beban Lain', 'header', 'debit'],
            ['7-1010', 'Pajak & Perizinan', 'expense', 'debit'],
            ['7-1020', 'Asuransi', 'expense', 'debit'],
            ['7-1030', 'Beban Bank', 'expense', 'debit'],
        ];

        foreach ($accounts as [$code, $name, $type, $normal]) {
            ChartOfAccount::updateOrCreate(
                ['property_id' => $property->id, 'code' => $code],
                ['name' => $name, 'type' => $type, 'normal_balance' => $normal, 'is_system' => true, 'is_active' => true]
            );
        }
    }
}
