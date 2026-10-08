<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FolioPayment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REFUNDED = 'refunded';

    /** Allowed forward transitions (payment state machine). */
    public const TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_PAID, self::STATUS_FAILED],
        self::STATUS_PAID => [self::STATUS_REFUNDED],
        self::STATUS_FAILED => [],
        self::STATUS_REFUNDED => [],
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'mdr_amount' => 'decimal:2',
        'is_void' => 'boolean',
        'gateway_payload' => 'array',
    ];

    public function folio()
    {
        return $this->belongsTo(Folio::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function shift()
    {
        return $this->belongsTo(CashierShift::class, 'shift_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function isSettled(): bool
    {
        return $this->status === self::STATUS_PAID && ! $this->is_void;
    }

    public function markPaid(?array $payload = null): bool
    {
        if (! $this->canTransitionTo(self::STATUS_PAID)) {
            return false;
        }
        $this->status = self::STATUS_PAID;
        if ($payload !== null) {
            $this->gateway_payload = array_merge($this->gateway_payload ?? [], $payload);
        }
        $this->save();

        return true;
    }

    public function markFailed(?array $payload = null): bool
    {
        if (! $this->canTransitionTo(self::STATUS_FAILED)) {
            return false;
        }
        $this->status = self::STATUS_FAILED;
        if ($payload !== null) {
            $this->gateway_payload = array_merge($this->gateway_payload ?? [], $payload);
        }
        $this->save();

        return true;
    }
}
