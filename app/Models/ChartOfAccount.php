<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $table = 'chart_of_accounts';
    protected $guarded = ['id'];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function children() { return $this->hasMany(self::class, 'parent_id'); }
    public function lines() { return $this->hasMany(JournalLine::class, 'account_id'); }
}
