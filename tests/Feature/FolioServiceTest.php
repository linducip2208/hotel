<?php

use App\Models\Folio;
use App\Models\Property;
use App\Services\Fo\FolioService;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'T2', 'slug' => 't2', 'region_code' => 'ID-JK', 'total_rooms' => 5, 'is_active' => true,
    ]);
    $this->folio = Folio::create([
        'property_id' => $this->property->id, 'folio_no' => 'F-T-001', 'type' => 'guest', 'status' => 'open', 'currency' => 'IDR',
    ]);
    // Seed COA
    foreach ([['1-1010', 'Kas', 'asset', 'debit'], ['1-1100', 'Piutang', 'asset', 'debit']] as [$c, $n, $t, $b]) {
        \App\Models\ChartOfAccount::create(['property_id' => $this->property->id, 'code' => $c, 'name' => $n, 'type' => $t, 'normal_balance' => $b, 'is_system' => true, 'is_active' => true]);
    }
});

it('posts charge and recalculates folio', function () {
    $svc = app(FolioService::class);
    $svc->postCharge($this->folio, [
        'description' => 'Room', 'category' => 'room', 'amount' => 500000, 'is_taxable' => true, 'tax_code' => 'PB1',
    ]);
    $f = $this->folio->fresh();
    expect((float) $f->total_charges)->toEqual(550000.0); // 500k + 50k PB1 (default 10%)
    expect((float) $f->balance)->toEqual(550000.0);
});

it('posts payment and reduces balance', function () {
    $svc = app(FolioService::class);
    $svc->postCharge($this->folio, ['description' => 'X', 'category' => 'other', 'amount' => 200000, 'is_taxable' => false]);
    $svc->postPayment($this->folio, ['amount' => 200000, 'method' => 'cash']);
    expect((float) $this->folio->fresh()->balance)->toEqual(0.0);
});

it('applies discount as negative charge', function () {
    $svc = app(FolioService::class);
    $svc->postCharge($this->folio, ['description' => 'X', 'category' => 'other', 'amount' => 500000, 'is_taxable' => false]);
    $svc->applyDiscount($this->folio, 100000, 'Loyalty member');
    expect((float) $this->folio->fresh()->total_charges)->toEqual(400000.0);
});
