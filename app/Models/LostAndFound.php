<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostAndFound extends Model
{
    use HasFactory;

    protected $table = 'lost_and_found';
    protected $guarded = ['id'];

    protected $casts = [
        'found_date' => 'date',
        'claimed_date' => 'date',
    ];

    public function property()       { return $this->belongsTo(Property::class); }
    public function room()           { return $this->belongsTo(Room::class); }
    public function foundByUser()    { return $this->belongsTo(User::class, 'found_by_user_id'); }
    public function claimedByGuest() { return $this->belongsTo(Guest::class, 'claimed_by_guest_id'); }
}
