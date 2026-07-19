<?php

declare(strict_types=1);

namespace App\Services\Indonesia;

use App\Models\Property;
use App\Models\Reservation;
use App\Models\Employee;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class SipgarReportService
{
    /**
     * Generate monthly SIPGAR report data for Kemenparekraf.
     */
    public function generateMonthlyReport(int $propertyId, Carbon $month): array
    {
        $property = Property::findOrFail($propertyId);
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $totalRooms = \App\Models\Room::where('property_id', $propertyId)
            ->where('is_active', true)->count();

        $totalRoomNights = $totalRooms * $start->daysInMonth;

        $occupiedRoomNights = Reservation::where('property_id', $propertyId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('check_in', [$start, $end])
                  ->orWhereBetween('check_out', [$start, $end])
                  ->orWhere(function ($q) use ($start, $end) {
                      $q->where('check_in', '<', $start)->where('check_out', '>', $end);
                  });
            })
            ->get()
            ->sum(function (Reservation $r) use ($start, $end) {
                $checkIn = max($r->check_in, $start);
                $checkOut = min($r->check_out, $end);
                return max(0, $checkIn->diffInDays($checkOut));
            });

        $occupancyPct = $totalRoomNights > 0
            ? round(($occupiedRoomNights / $totalRoomNights) * 100, 2)
            : 0;

        $domesticGuests = Reservation::where('property_id', $propertyId)
            ->whereBetween('check_in', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->whereHas('primaryGuest', fn ($q) => $q->where('country', 'ID')->orWhereNull('country'))
            ->count();

        $foreignGuests = Reservation::where('property_id', $propertyId)
            ->whereBetween('check_in', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->whereHas('primaryGuest', fn ($q) => $q->where('country', '!=', 'ID')->whereNotNull('country'))
            ->count();

        $revenue = Reservation::where('property_id', $propertyId)
            ->whereBetween('check_in', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');

        $employeeCount = Employee::where('property_id', $propertyId)
            ->where('is_active', true)->count();

        return [
            'property_name' => $property->name,
            'star_rating' => $property->star_rating ?? 3,
            'report_month' => $month->format('Y-m'),
            'total_rooms' => $totalRooms,
            'total_room_nights' => $totalRoomNights,
            'occupied_room_nights' => $occupiedRoomNights,
            'occupancy_pct' => $occupancyPct,
            'domestic_guests' => $domesticGuests,
            'foreign_guests' => $foreignGuests,
            'total_guests' => $domesticGuests + $foreignGuests,
            'total_revenue' => round((float) $revenue, 2),
            'employee_count' => $employeeCount,
            'address' => $property->address ?? '',
            'city' => $property->city ?? '',
            'province' => $property->province ?? '',
        ];
    }

    /** Export SIPGAR report as Excel file. */
    public function exportExcel(int $propertyId, Carbon $month): string
    {
        $data = $this->generateMonthlyReport($propertyId, $month);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('SIPGAR ' . $month->format('Y-m'));
        $sheet->setCellValue('A1', 'LAPORAN SIPGAR — KEMENPAREKRAF');
        $sheet->setCellValue('A2', 'Periode: ' . $month->format('F Y'));
        $sheet->mergeCells('A1:B1');
        $sheet->mergeCells('A2:B2');

        $row = 4;
        $fields = [
            'Nama Hotel' => $data['property_name'],
            'Bintang' => $data['star_rating'],
            'Alamat' => $data['address'],
            'Kota' => $data['city'],
            'Provinsi' => $data['province'],
            'Jumlah Kamar' => $data['total_rooms'],
            'Okupansi (%)' => $data['occupancy_pct'],
            'Tamu Domestik' => $data['domestic_guests'],
            'Tamu Mancanegara' => $data['foreign_guests'],
            'Total Tamu' => $data['total_guests'],
            'Total Revenue (Rp)' => $data['total_revenue'],
            'Jumlah Karyawan' => $data['employee_count'],
        ];

        foreach ($fields as $label => $value) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("B{$row}", (string) $value);
            $row++;
        }

        foreach (['A', 'B'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = "sipgar_{$data['property_name']}_{$month->format('Y_m')}.xlsx";
        $path = storage_path("app/reports/{$fileName}");

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $path;
    }

    /** Export as CSV for simpler integration. */
    public function exportCsv(int $propertyId, Carbon $month): string
    {
        $data = $this->generateMonthlyReport($propertyId, $month);
        $fileName = "sipgar_{$data['property_name']}_{$month->format('Y_m')}.csv";
        $path = storage_path("app/reports/{$fileName}");

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $fp = fopen($path, 'w');
        fputcsv($fp, ['Field', 'Value']);
        foreach ($data as $key => $value) {
            fputcsv($fp, [$key, $value]);
        }
        fclose($fp);

        return $path;
    }
}
