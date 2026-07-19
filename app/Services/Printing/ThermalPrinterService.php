<?php

namespace App\Services\Printing;

use Illuminate\Support\Facades\Log;

class ThermalPrinterService
{
    protected ?string $printerName;
    protected ?string $printerInterface;
    protected ?string $printerHost;
    protected ?int $printerPort;
    protected int $charWidth = 32;

    public function __construct(?string $printerName = null, ?string $printerHost = null, ?int $printerPort = null)
    {
        $property = app('current_property');
        $settings = $property?->settings ?? [];

        $this->printerName = $printerName ?? $settings['thermal_printer_name'] ?? null;
        $this->printerInterface = $settings['thermal_printer_interface'] ?? 'network';
        $this->printerHost = $printerHost ?? $settings['thermal_printer_host'] ?? null;
        $this->printerPort = $printerPort ?? (int)($settings['thermal_printer_port'] ?? 9100);
    }

    public function printFolioReceipt($folioId): array
    {
        $folio = \App\Models\Folio::with(['reservation.rooms.room', 'reservation.primaryGuest', 'charges', 'payments'])
            ->findOrFail($folioId);

        $property = app('current_property');

        $lines = [];
        $lines[] = $this->center($property->name ?? config('app.name'));
        $lines[] = $this->center($property->address_line1 ?? '');
        $lines[] = $this->center('NPWP: ' . ($property->npwp ?? '-'));
        $lines[] = str_repeat('-', $this->charWidth);
        $lines[] = 'No: ' . $folio->folio_no;
        $lines[] = 'Tamu: ' . ($folio->reservation->primaryGuest->full_name ?? '-');
        $lines[] = 'Kamar: ' . $folio->reservation->rooms->map(fn($rr) => $rr->room->number ?? $rr->id)->join(', ');
        $lines[] = 'Check-in: ' . optional($folio->reservation->check_in)->format('d/m/Y H:i');
        $lines[] = 'Check-out: ' . optional($folio->reservation->check_out)->format('d/m/Y H:i');
        $lines[] = str_repeat('-', $this->charWidth);

        foreach ($folio->charges as $charge) {
            $label = $charge->description ?? $charge->category;
            $lines[] = sprintf('%-22s %10s', mb_substr($label, 0, 22), number_format($charge->amount, 0, ',', '.'));
        }

        $lines[] = str_repeat('-', $this->charWidth);
        $total = $folio->charges->sum('amount');
        $paid = $folio->payments->sum('amount');
        $balance = $total - $paid;

        $lines[] = sprintf('%-16s %16s', 'TOTAL', number_format($total, 0, ',', '.'));
        $lines[] = sprintf('%-16s %16s', 'DIBAYAR', number_format($paid, 0, ',', '.'));
        $lines[] = sprintf('%-16s %16s', 'SISA', number_format($balance, 0, ',', '.'));

        $lines[] = '';
        $lines[] = $this->center('Terima kasih');
        $lines[] = $this->center(now()->format('d/m/Y H:i:s'));
        $lines[] = '';
        $lines[] = '';
        $lines[] = '';
        $lines[] = '';

        $this->sendRaw(implode("\n", $lines));

        return ['success' => true, 'lines' => count($lines)];
    }

    public function printPosOrder($orderId): array
    {
        $order = \App\Models\PosOrder::with(['outlet', 'items.menuItem', 'payments', 'table'])
            ->findOrFail($orderId);

        $property = app('current_property');

        $lines = [];
        $lines[] = $this->center($property->name ?? config('app.name'));
        $lines[] = $this->center($order->outlet->name ?? 'POS');
        $lines[] = str_repeat('-', $this->charWidth);
        $lines[] = 'Order: #' . ($order->order_no ?? $order->id);
        $lines[] = 'Meja: ' . ($order->table->name ?? '-');
        $lines[] = 'Tanggal: ' . $order->created_at->format('d/m/Y H:i');
        $lines[] = str_repeat('-', $this->charWidth);

        foreach ($order->items as $item) {
            $name = $item->menuItem->name ?? 'Item';
            $qty = $item->quantity;
            $price = $item->unit_price;
            $lines[] = sprintf('%.0fx %s', $qty, mb_substr($name, 0, 22));
            $lines[] = sprintf('%28s', '@' . number_format($price, 0, ',', '.') . ' = ' . number_format($qty * $price, 0, ',', '.'));
        }

        $lines[] = str_repeat('-', $this->charWidth);
        $lines[] = sprintf('%-16s %16s', 'SUBTOTAL', number_format($order->subtotal, 0, ',', '.'));
        if ($order->discount > 0) {
            $lines[] = sprintf('%-16s %16s', 'DISKON', number_format($order->discount, 0, ',', '.'));
        }
        if ($order->service_charge > 0) {
            $lines[] = sprintf('%-16s %16s', 'SERVICE (10%)', number_format($order->service_charge, 0, ',', '.'));
        }
        if ($order->tax_total > 0) {
            $lines[] = sprintf('%-16s %16s', 'PAJAK', number_format($order->tax_total, 0, ',', '.'));
        }
        $lines[] = sprintf('%-16s %16s', 'GRAND TOTAL', number_format($order->grand_total, 0, ',', '.'));
        $lines[] = '';
        $lines[] = $this->center('Pembayaran');

        foreach ($order->payments as $payment) {
            $lines[] = sprintf('%-16s %16s', $payment->method, number_format($payment->amount, 0, ',', '.'));
        }

        $lines[] = '';
        $lines[] = $this->center('Terima kasih');
        $lines[] = $this->center(now()->format('d/m/Y H:i:s'));
        $lines[] = '';
        $lines[] = '';
        $lines[] = '';

        $this->sendRaw(implode("\n", $lines));

        return ['success' => true, 'lines' => count($lines)];
    }

    public function printKitchenOrder($orderId): array
    {
        $order = \App\Models\PosOrder::with(['outlet', 'items.menuItem', 'table'])
            ->findOrFail($orderId);

        $lines = [];
        $lines[] = $this->center('*** DAPUR ***');
        $lines[] = str_repeat('-', $this->charWidth);
        $lines[] = 'Order: #' . ($order->order_no ?? $order->id);
        $lines[] = 'Outlet: ' . ($order->outlet->name ?? '-');
        $lines[] = 'Meja: ' . ($order->table->name ?? '-');
        $lines[] = str_repeat('-', $this->charWidth);

        foreach ($order->items as $item) {
            $name = $item->menuItem->name ?? 'Item';
            $qty = $item->quantity;
            $lines[] = sprintf('%-3s %s', $qty . 'x', mb_strtoupper(mb_substr($name, 0, 27)));

            if ($item->notes) {
                $lines[] = '  > ' . mb_substr($item->notes, 0, 28);
            }
        }

        $lines[] = str_repeat('-', $this->charWidth);
        $lines[] = $this->center(now()->format('d/m/Y H:i:s'));
        $lines[] = '';
        $lines[] = '';

        $this->sendRaw(implode("\n", $lines));

        return ['success' => true, 'lines' => count($lines)];
    }

    public function testPrint(): array
    {
        $property = app('current_property');

        $lines = [];
        $lines[] = $this->center($property->name ?? config('app.name'));
        $lines[] = $this->center('*** TEST PRINT ***');
        $lines[] = str_repeat('-', $this->charWidth);
        $lines[] = 'Printer: ' . ($this->printerName ?? 'Thermal Printer');
        $lines[] = 'Host: ' . ($this->printerHost ?? '-');
        $lines[] = 'Port: ' . ($this->printerPort ?? '-');
        $lines[] = str_repeat('-', $this->charWidth);
        $lines[] = 'Waktu: ' . now()->format('d/m/Y H:i:s');
        $lines[] = '';
        $lines[] = $this->center('Test OK!');
        $lines[] = '';
        $lines[] = '';
        $lines[] = '';

        $this->sendRaw(implode("\n", $lines));

        return ['success' => true, 'lines' => count($lines)];
    }

    protected function center(string $text): string
    {
        $len = mb_strwidth($text);
        if ($len >= $this->charWidth) {
            return $text;
        }
        $pad = intdiv($this->charWidth - $len, 2);
        return str_repeat(' ', max(0, $pad)) . $text;
    }

    public function sendRaw(string $data): void
    {
        if (!$this->printerHost) {
            Log::info('Thermal printer not configured, skipping print', [
                'data_preview' => mb_substr($data, 0, 100),
            ]);
            return;
        }

        try {
            $errno = 0;
            $errstr = '';
            $socket = @fsockopen($this->printerHost, $this->printerPort, $errno, $errstr, 5);

            if ($socket) {
                fwrite($socket, $data);
                fwrite($socket, "\x1B\x69");
                fclose($socket);
                Log::info('Thermal print sent', [
                    'host' => $this->printerHost,
                    'port' => $this->printerPort,
                    'length' => strlen($data),
                ]);
            } else {
                Log::error('Thermal printer connection failed', [
                    'host' => $this->printerHost,
                    'port' => $this->printerPort,
                    'errno' => $errno,
                    'errstr' => $errstr,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Thermal printer error: ' . $e->getMessage());
        }
    }
}
