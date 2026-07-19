<?php

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\Property;
use App\Services\Accounting\JournalPoster;

beforeEach(function () {
    $this->property = Property::create([
        'name' => 'Journal Hotel', 'slug' => 'journal-hotel', 'region_code' => 'ID-JK', 'total_rooms' => 10, 'is_active' => true,
    ]);
    // Minimal COA: cash (asset/debit) + revenue (revenue/credit)
    $this->cash = ChartOfAccount::create(['property_id' => $this->property->id, 'code' => '1101', 'name' => 'Cash', 'type' => 'asset', 'normal_balance' => 'debit', 'is_active' => true]);
    $this->rev = ChartOfAccount::create(['property_id' => $this->property->id, 'code' => '4001', 'name' => 'Room Revenue', 'type' => 'revenue', 'normal_balance' => 'credit', 'is_active' => true]);
    $this->svc = app(JournalPoster::class);
});

it('posts a balanced journal entry', function () {
    $entry = $this->svc->post($this->property->id, 'Test room revenue', [
        ['account_code' => '1101', 'debit' => 500000, 'credit' => 0],
        ['account_code' => '4001', 'debit' => 0, 'credit' => 500000],
    ]);

    expect($entry->entry_no)->toStartWith('JE-')
        ->and($entry->status)->toBe('posted')
        ->and((float) $entry->total_debit)->toBe(500000.0)
        ->and((float) $entry->total_credit)->toBe(500000.0)
        ->and($entry->lines()->count())->toBe(2);
});

it('throws on unbalanced entry', function () {
    expect(fn () => $this->svc->post($this->property->id, 'Bad entry', [
        ['account_code' => '1101', 'debit' => 500000, 'credit' => 0],
        ['account_code' => '4001', 'debit' => 0, 'credit' => 300000],
    ]))->toThrow(\RuntimeException::class, 'Unbalanced journal');
});

it('throws on unknown COA code', function () {
    expect(fn () => $this->svc->post($this->property->id, 'Unknown code', [
        ['account_code' => '9999', 'debit' => 100000, 'credit' => 0],
        ['account_code' => '4001', 'debit' => 0, 'credit' => 100000],
    ]))->toThrow(\RuntimeException::class, 'Unknown COA code');
});

it('assigns source_type and source_id when provided', function () {
    $entry = $this->svc->post($this->property->id, 'Check-out settlement', [
        ['account_code' => '1101', 'debit' => 200000, 'credit' => 0],
        ['account_code' => '4001', 'debit' => 0, 'credit' => 200000],
    ], 'folio', 42);

    expect($entry->source_type)->toBe('folio')
        ->and($entry->source_id)->toBe(42);
});

it('journal entries have unique entry_no', function () {
    $e1 = $this->svc->post($this->property->id, 'Entry 1', [
        ['account_code' => '1101', 'debit' => 100000, 'credit' => 0],
        ['account_code' => '4001', 'debit' => 0, 'credit' => 100000],
    ]);
    $e2 = $this->svc->post($this->property->id, 'Entry 2', [
        ['account_code' => '1101', 'debit' => 100000, 'credit' => 0],
        ['account_code' => '4001', 'debit' => 0, 'credit' => 100000],
    ]);

    expect($e1->entry_no)->not->toBe($e2->entry_no);
});
