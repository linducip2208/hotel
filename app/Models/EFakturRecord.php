<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EFakturRecord extends Model
{
    use HasFactory;

    protected $table = 'efaktur_records';

    protected $guarded = ['id'];

    protected $casts = [
        'dpp' => 'decimal:2',
        'ppn' => 'decimal:2',
        'request_payload' => 'json',
        'response_payload' => 'json',
        'sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function invoice()
    {
        return $this->belongsTo(ArInvoice::class, 'invoice_id');
    }

    public function source()
    {
        return $this->morphTo();
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function markSent(string $nomorFaktur, array $response, string $kodeStatus): void
    {
        $this->update([
            'nomor_faktur' => $nomorFaktur,
            'status' => $kodeStatus,
            'response_payload' => $response,
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markFailed(string $errorMessage, ?array $response = null): void
    {
        $this->update([
            'status' => 'failed',
            'response_payload' => $response,
            'sent_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    public function markCancelled(string $reason, int $userId): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by_user_id' => $userId,
            'cancel_reason' => $reason,
        ]);
    }
}
