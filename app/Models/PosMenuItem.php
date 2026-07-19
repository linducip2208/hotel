<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosMenuItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'cogs' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
        'modifiers' => 'array',
        'photos' => 'array',
    ];

    public function outlet() { return $this->belongsTo(PosOutlet::class, 'outlet_id'); }
    public function category() { return $this->belongsTo(PosCategory::class, 'category_id'); }
    public function recipes()  { return $this->hasMany(PosRecipe::class, 'menu_item_id'); }
}
