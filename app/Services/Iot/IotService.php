<?php

namespace App\Services\Iot;

use App\Models\IotDevice;
use App\Models\IotCommand;
use App\Models\IotEnergyLog;
use App\Models\Property;
use App\Models\Room;

class IotService
{
    public function getRoomDevices(Room $room): array
    {
        return IotDevice::where('room_id', $room->id)
            ->where('status', '!=', 'offline')
            ->get()->toArray();
    }

    public function sendCommand(IotDevice $device, string $command, array $payload = [], ?int $userId = null, string $trigger = 'manual'): IotCommand
    {
        $cmd = IotCommand::create([
            'property_id' => $device->property_id,
            'iot_device_id' => $device->id,
            'command' => $command,
            'payload' => $payload,
            'status' => 'pending',
            'triggered_by_user_id' => $userId,
            'trigger' => $trigger,
        ]);

        $state = $device->current_state ?? [];
        $state = array_merge($state, $payload);
        $device->update(['current_state' => $state]);

        $cmd->update(['status' => 'executed']);
        return $cmd;
    }

    public function energySavingMode(Room $room): void
    {
        $devices = IotDevice::where('room_id', $room->id)->get();
        foreach ($devices as $device) {
            match ($device->device_type) {
                'thermostat' => $this->sendCommand($device, 'set_temperature', ['temperature' => 28], null, 'energy_saving'),
                'lighting' => $this->sendCommand($device, 'turn_off', [], null, 'energy_saving'),
                'tv' => $this->sendCommand($device, 'turn_off', [], null, 'energy_saving'),
                default => null,
            };
        }
    }

    public function guestWelcomeMode(Room $room): void
    {
        $devices = IotDevice::where('room_id', $room->id)->get();
        foreach ($devices as $device) {
            match ($device->device_type) {
                'thermostat' => $this->sendCommand($device, 'set_temperature', ['temperature' => 23], null, 'checkin'),
                'lighting' => $this->sendCommand($device, 'turn_on', ['brightness' => 70], null, 'checkin'),
                default => null,
            };
        }
    }

    public function getEnergyReport(Property $property, string $from, string $to): array
    {
        $logs = IotEnergyLog::where('property_id', $property->id)
            ->whereBetween('log_date', [$from, $to])
            ->with('room')
            ->get();

        $totalKwh = $logs->sum('energy_kwh');
        $totalCost = $logs->sum('cost_estimate');

        $byRoom = $logs->groupBy('room_id')->map(function ($group) {
            $first = $group->first();
            return [
                'room' => $first->room?->number ?? 'Unknown',
                'kwh' => round((float) $group->sum('energy_kwh'), 2),
                'cost' => round((float) $group->sum('cost_estimate'), 0),
            ];
        })->sortByDesc('kwh')->values()->toArray();

        return compact('totalKwh', 'totalCost', 'byRoom');
    }
}
