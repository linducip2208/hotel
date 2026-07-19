<?php

use App\Services\Audit\AuditLogger;

it('builds chain with previous_hash linking', function () {
    $logger = app(AuditLogger::class);
    $e1 = $logger->record('test.first', null, ['n' => 1]);
    $e2 = $logger->record('test.second', null, ['n' => 2]);
    $e3 = $logger->record('test.third', null, ['n' => 3]);

    expect($e1->previous_hash)->toBeNull();
    expect($e2->previous_hash)->toBe($e1->entry_hash);
    expect($e3->previous_hash)->toBe($e2->entry_hash);

    expect($e1->verifyHash())->toBeTrue();
    expect($e2->verifyHash())->toBeTrue();
    expect($e3->verifyHash())->toBeTrue();
});

it('detects tampering in audit log', function () {
    $logger = app(AuditLogger::class);
    $entry = $logger->record('test.tamper', null, ['secret' => 'original']);

    // Simulate tampering — change after but keep entry_hash
    $entry->after = ['tampered' => true];
    $entry->saveQuietly();

    expect($entry->fresh()->verifyHash())->toBeFalse();
});
