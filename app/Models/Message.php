<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['attachments' => 'array', 'raw_payload' => 'array'];

    public function thread() { return $this->belongsTo(MessageThread::class, 'thread_id'); }
}
