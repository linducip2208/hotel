<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\Printing\ThermalPrinterService;

class PrintController extends Controller
{
    public function printFolio(int $id)
    {
        $printer = new ThermalPrinterService();
        $result = $printer->printFolioReceipt($id);

        return back()->with('success', 'Folio dikirim ke printer. (' . $result['lines'] . ' baris)');
    }

    public function printPosOrder(int $id)
    {
        $printer = new ThermalPrinterService();
        $result = $printer->printPosOrder($id);

        return back()->with('success', 'Struk POS dikirim ke printer. (' . $result['lines'] . ' baris)');
    }

    public function printKitchenOrder(int $id)
    {
        $printer = new ThermalPrinterService();
        $result = $printer->printKitchenOrder($id);

        return back()->with('success', 'Order dapur dikirim ke printer. (' . $result['lines'] . ' baris)');
    }

    public function testPrint()
    {
        $printer = new ThermalPrinterService();
        $result = $printer->testPrint();

        return back()->with('success', 'Test print dikirim. (' . $result['lines'] . ' baris)');
    }
}
