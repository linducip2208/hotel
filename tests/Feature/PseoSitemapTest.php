<?php

it('serves sitemap index xml without license', function () {
    $response = $this->get('/sitemap.xml');
    $response->assertStatus(200);
});

it('serves sitemap group xml', function () {
    $response = $this->get('/sitemap-rooms.xml');
    $response->assertStatus(200);
});
