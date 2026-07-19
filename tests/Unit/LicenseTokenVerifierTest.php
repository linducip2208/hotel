<?php

use App\Services\License\TokenVerifier;

it('returns null for invalid token', function () {
    $verifier = new TokenVerifier();
    expect($verifier->verify('not-a-real-jwt'))->toBeNull();
});
