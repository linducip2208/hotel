<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderFeatureAssignment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = ['config' => 'array'];

    public function property() { return $this->belongsTo(Property::class); }
    public function provider() { return $this->belongsTo(Provider::class); }
}
