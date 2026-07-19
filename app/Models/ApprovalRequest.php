<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function property()  { return $this->belongsTo(Property::class); }
    public function requester() { return $this->belongsTo(User::class, 'requester_id'); }
    public function approver()  { return $this->belongsTo(User::class, 'approver_id'); }
}
