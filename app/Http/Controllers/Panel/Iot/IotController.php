<?php

namespace App\Http\Controllers\Panel\Iot;

use App\Http\Controllers\Controller;
use App\Models\IotDevice;
use App\Models\IotEnergyLog;
use App\Models\Room;
use App\Services\Iot\IotService;
use Illuminate\Http\Request;

class IotController extends Controller
{
    public function index()
    {
        $property = app('current_property');
        $rooms = Room::where('property_id', $property->id)
            ->with(['iotDevices' => fn($q) => $q->where('status', '!=', 'offline')])
            ->orderBy('floor')->orderBy('number')
            ->get();

        $deviceCounts = [
            'total' => IotDevice::where('property_id', $property->id)->count(),
            'online' => IotDevice::where('property_id', $property->id)->where('status', 'online')->count(),
            'offline' => IotDevice::where('property_id', $property->id)->where('status', 'offline')->count(),
        ];

        return view('panel.iot.dashboard', compact('rooms', 'deviceCounts'));
    }

    public function room($roomId, IotService $iotService)
    {
        $property = app('current_property');
        $room = Room::where('property_id', $property->id)->findOrFail($roomId);
        $devices = IotDevice::where('room_id', $roomId)->with('commands')->get();

        return view('panel.iot.room', compact('room', 'devices'));
    }

    public function command(Request $request, IotService $iotService)
    {
        $request->validate([
            'device_id' => 'required|integer|exists:iot_devices,id',
            'command' => 'required|string',
            'payload' => 'nullable|array',
        ]);

        $device = IotDevice::findOrFail($request->device_id);
        $iotService->sendCommand(
            $device,
            $request->command,
            $request->payload ?? [],
            auth()->id(),
            $request->trigger ?? 'manual'
        );

        return back()->with('success', "Command '{$request->command}' sent to {$device->name}.");
    }

    public function energy(Request $request)
    {
        $property = app('current_property');
        $from = $request->query('from', now()->subDays(30)->toDateString());
        $to = $request->query('to', now()->toDateString());

        $report = (new IotService)->getEnergyReport($property, $from, $to);

        return view('panel.iot.energy', compact('report', 'from', 'to'));
    }
}
