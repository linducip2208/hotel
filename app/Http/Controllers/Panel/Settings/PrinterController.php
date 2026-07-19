<?php

namespace App\Http\Controllers\Panel\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrinterController extends Controller
{
    public function edit()
    {
        $property = app('current_property');
        $settings = $property->settings ?? [];

        return view('panel.settings.printer', [
            'property' => $property,
            'printerName' => $settings['thermal_printer_name'] ?? '',
            'printerInterface' => $settings['thermal_printer_interface'] ?? 'network',
            'printerHost' => $settings['thermal_printer_host'] ?? '',
            'printerPort' => $settings['thermal_printer_port'] ?? '9100',
            'kitchenPrinterHost' => $settings['thermal_kitchen_printer_host'] ?? '',
            'kitchenPrinterPort' => $settings['thermal_kitchen_printer_port'] ?? '9100',
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'thermal_printer_name' => ['nullable', 'string', 'max:255'],
            'thermal_printer_interface' => ['required', 'in:network,usb,serial'],
            'thermal_printer_host' => ['nullable', 'string', 'max:255'],
            'thermal_printer_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'thermal_kitchen_printer_host' => ['nullable', 'string', 'max:255'],
            'thermal_kitchen_printer_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ]);

        $property = app('current_property');
        $settings = $property->settings ?? [];
        $settings = array_merge($settings, $data);
        $property->update(['settings' => $settings]);

        return back()->with('success', 'Pengaturan printer disimpan.');
    }
}
