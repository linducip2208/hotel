<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinenCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'par_level' => 'integer',
        'current_stock' => 'integer',
        'damaged_count' => 'integer',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function transactions() { return $this->hasMany(LaundryTransaction::class, 'linen_category_id'); }
    public function uniformAssignments() { return $this->hasMany(UniformAssignment::class, 'linen_category_id'); }

    public function getParPctAttribute(): int
    {
        if ($this->par_level <= 0) return 0;
        return min(100, (int) round(($this->current_stock / $this->par_level) * 100));
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->current_stock <= 0) return 'empty';
        if ($this->par_level > 0 && $this->current_stock < $this->par_level) return 'below_par';
        if ($this->par_level > 0 && $this->current_stock === $this->par_level) return 'at_par';
        return 'above_par';
    }
}
