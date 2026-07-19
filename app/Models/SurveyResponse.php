<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['answers' => 'array', 'submitted_at' => 'datetime'];

    public function survey()      { return $this->belongsTo(Survey::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function guest()       { return $this->belongsTo(Guest::class); }
}
