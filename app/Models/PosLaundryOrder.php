<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosLaundryOrder extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function guest() { return $this->belongsTo(Guest::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function room() { return $this->belongsTo(Room::class); }
    public function receivedBy() { return $this->belongsTo(User::class, 'received_by'); }
    public function deliveredBy() { return $this->belongsTo(User::class, 'delivered_by'); }

    public static function laundryItems(): array
    {
        return [
            ['key' => 'shirt', 'name' => 'Shirt', 'wash' => 8000, 'dry_clean' => 15000, 'iron' => 5000],
            ['key' => 'pants', 'name' => 'Pants', 'wash' => 10000, 'dry_clean' => 18000, 'iron' => 6000],
            ['key' => 'dress', 'name' => 'Dress', 'wash' => 12000, 'dry_clean' => 22000, 'iron' => 8000],
            ['key' => 'jacket', 'name' => 'Jacket', 'wash' => 15000, 'dry_clean' => 25000, 'iron' => 10000],
            ['key' => 'towel', 'name' => 'Towel', 'wash' => 5000, 'dry_clean' => null, 'iron' => null],
            ['key' => 'bedsheet', 'name' => 'Bed Sheet', 'wash' => 12000, 'dry_clean' => 20000, 'iron' => 6000],
            ['key' => 'underwear', 'name' => 'Underwear', 'wash' => 4000, 'dry_clean' => null, 'iron' => null],
        ];
    }

    public function statuses(): array
    {
        return ['received', 'washing', 'drying', 'folding', 'ready', 'delivered'];
    }
}
