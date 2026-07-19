<?php

use App\Services\License\FingerprintGenerator;

it('generates stable fingerprint format', function () {
    $gen = new FingerprintGenerator();
    $fp = $gen->generate('test-install-id');
    expect($fp)->toStartWith('sha256:');
    expect(strlen($fp))->toBe(71); // sha256: + 64 hex
});

it('generates unique uuid install id', function () {
    $gen = new FingerprintGenerator();
    expect($gen->newInstallId())->not->toBe($gen->newInstallId());
});
