<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AriSyncLog extends Model
{
    use HasFactory;

    protected $table = 'ari_sync_log';
    protected $guarded = ['id'];

    protected $casts = [
        'payload_summary' => 'array',
        'response_summary' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function channel() { return $this->belongsTo(Channel::class); }
}
