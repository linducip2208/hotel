<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLogCheckpoint extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['checkpoint_date' => 'date', 'exported_at' => 'datetime'];
}
