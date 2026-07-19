<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostFoundItem extends Model
{
    use HasFactory;

    protected $table = 'lost_found_items';
    protected $guarded = ['id'];

    protected $casts = [
        'found_at'   => 'datetime',
        'claimed_at' => 'datetime',
        'photos'     => 'array',
    ];

    public function property()       { return $this->belongsTo(Property::class); }
    public function room()           { return $this->belongsTo(Room::class); }
    public function foundByUser()    { return $this->belongsTo(User::class, 'found_by_user_id'); }
    public function claimedByGuest() { return $this->belongsTo(Guest::class, 'claimed_by_guest_id'); }

    public static function generateItemNumber(): string
    {
        $prefix = 'LF-' . now()->format('Ymd') . '-';
        $last = static::where('item_number', 'like', $prefix . '%')
            ->orderByDesc('item_number')
            ->first();
        $seq = $last ? (int) substr($last->item_number, -4) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function isExpired(): bool
    {
        return $this->status === 'found'
            && $this->found_at
            && $this->found_at->addDays($this->disposal_days)->isPast();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'found'    => 'Ditemukan',
            'claimed'  => 'Diklaim',
            'disposed' => 'Dibuang',
            'donated'  => 'Disumbangkan',
            'returned' => 'Dikembalikan',
            default    => ucfirst($this->status),
        };
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'electronics' => 'Elektronik',
            'clothing'    => 'Pakaian',
            'jewelry'     => 'Perhiasan',
            'documents'   => 'Dokumen',
            'toys'        => 'Mainan',
            'keys'        => 'Kunci',
            'other'       => 'Lainnya',
            default       => $this->category,
        };
    }

    public function categoryColor(): string
    {
        return match ($this->category) {
            'electronics' => 'bg-purple-100 text-purple-700',
            'clothing'    => 'bg-cyan-100 text-cyan-700',
            'jewelry'     => 'bg-amber-100 text-amber-700',
            'documents'   => 'bg-blue-100 text-blue-700',
            'toys'        => 'bg-pink-100 text-pink-700',
            'keys'        => 'bg-orange-100 text-orange-700',
            'other'       => 'bg-stone-100 text-stone-600',
            default       => 'bg-stone-100 text-stone-600',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'found'    => 'bg-amber-100 text-amber-700',
            'claimed'  => 'bg-blue-100 text-blue-700',
            'disposed' => 'bg-stone-100 text-stone-500',
            'donated'  => 'bg-emerald-100 text-emerald-700',
            'returned' => 'bg-emerald-100 text-emerald-700',
            default    => 'bg-stone-100 text-stone-500',
        };
    }
}
