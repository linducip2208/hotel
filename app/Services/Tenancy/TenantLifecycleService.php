<?php

namespace App\Services\Tenancy;

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TenantLifecycleService
{
    public const SUSPEND_GRACE_DAYS = 7;
    public const HARD_DELETE_DAYS = 90;

    public function processAll(): array
    {
        $stats = [
            'trial_notified' => 0,
            'trial_expired' => 0,
            'past_due_notified' => 0,
            'suspended' => 0,
            'churned' => 0,
        ];

        Tenant::query()->each(function (Tenant $t) use (&$stats) {
            // 1. Trial countdown notifications
            if ($t->isTrialing()) {
                $daysLeft = $t->trial_ends_at->diffInDays(now()->startOfDay(), false);
                if (in_array($daysLeft, [-7, -3, 0]) && ! $this->wasNotified($t, 'trial_d'.abs($daysLeft))) {
                    $this->notifyTrial($t, abs($daysLeft));
                    $stats['trial_notified']++;
                }
            }

            // 2. Trial expired → past_due
            if ($t->status === 'trial' && $t->trial_ends_at && $t->trial_ends_at->isPast()) {
                $t->update(['status' => 'past_due']);
                $t->logEvent('trial_expired');
                $stats['trial_expired']++;
            }

            // 3. Past_due grace expired → suspend (read-only)
            if ($t->status === 'past_due' && $t->trial_ends_at?->lte(now()->subDays(self::SUSPEND_GRACE_DAYS))) {
                $t->update(['status' => 'suspended', 'suspended_at' => now()]);
                $t->logEvent('suspended', ['reason' => 'past_due_grace_expired']);
                $stats['suspended']++;
            }

            // 4. Suspended > 90d → hard delete (mark churned, schedule data purge)
            if ($t->status === 'suspended' && $t->suspended_at?->lte(now()->subDays(self::HARD_DELETE_DAYS))) {
                $t->update(['status' => 'churned', 'churned_at' => now(), 'churn_reason' => 'auto_churn_after_suspension']);
                $t->logEvent('churned');
                $stats['churned']++;
            }
        });

        Log::info('Tenant lifecycle processed', $stats);
        return $stats;
    }

    protected function wasNotified(Tenant $t, string $event): bool
    {
        return collect($t->lifecycle_events ?? [])->contains(fn ($e) => $e['event'] === $event);
    }

    protected function notifyTrial(Tenant $t, int $days): void
    {
        $t->logEvent('trial_d'.$days, ['days_until_expiry' => $days]);
        // TODO: send email via NotificationDispatcher to $t->owner_email
    }
}
