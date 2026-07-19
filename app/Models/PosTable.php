<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTable extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean'];

    public function outlet() { return $this->belongsTo(PosOutlet::class, 'outlet_id'); }
}
