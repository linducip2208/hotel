<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageThread extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['last_message_at' => 'datetime'];

    public function property() { return $this->belongsTo(Property::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function messages() { return $this->hasMany(Message::class, 'thread_id'); }
    public function assignee() { return $this->belongsTo(User::class, 'assignee_id'); }
}
