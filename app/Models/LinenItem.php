<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinenItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'last_audit_at' => 'datetime',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function transactions() { return $this->hasMany(LinenTransaction::class, 'linen_item_id'); }

    public function getParLevelAttribute(): int
    {
        return (int) ceil($this->initial_stock * 3);
    }

    public function getStatusAttribute(): string
    {
        if ($this->current_stock <= $this->initial_stock) return 'deficit';
        if ($this->current_stock < $this->par_level) return 'low';
        return 'healthy';
    }
}
