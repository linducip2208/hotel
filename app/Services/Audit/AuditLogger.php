<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    public function record(string $action, ?Model $auditable = null, array $metadata = [], ?array $before = null, ?array $after = null): ?AuditLog
    {
        try {
            return DB::transaction(function () use ($action, $auditable, $metadata, $before, $after) {
                $user = auth()->user();

                $entry = AuditLog::create([
                    'property_id' => app()->bound('current_property') ? app('current_property')?->id : null,
                    'user_id' => $user?->id,
                    'user_type' => $user ? class_basename($user) : 'system',
                    'action' => $action,
                    'auditable_type' => $auditable?->getMorphClass(),
                    'auditable_id' => $auditable?->getKey(),
                    'before' => $before,
                    'after' => $after,
                    'ip' => request()?->ip(),
                    'user_agent' => request()?->userAgent(),
                    'request_id' => request()?->header('X-Request-Id'),
                    'metadata' => $metadata,
                ]);

                $previous = AuditLog::where('id', '<', $entry->id)->orderByDesc('id')->first();
                $entry->previous_hash = $previous?->entry_hash;
                $entry->entry_hash = $entry->computeHash($entry->previous_hash);
                $entry->save();

                return $entry;
            });
        } catch (\Throwable $e) {
            Log::channel('audit')->error('Audit log failed: '.$e->getMessage());
            return null;
        }
    }
}
