<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['questions' => 'array', 'is_active' => 'boolean'];

    public function property() { return $this->belongsTo(Property::class); }
    public function responses(){ return $this->hasMany(SurveyResponse::class); }
}
