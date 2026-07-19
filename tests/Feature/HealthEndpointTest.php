<?php

it('responds to /health', function () {
    $response = $this->get('/health');
    $response->assertStatus(200);
});
