<?php

test('api root endpoint returns success response', function () {
    $response = $this->getJson('/api/v1/');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
        ])
        ->assertJson([
            'message' => 'Hello World',
        ]);
});

test('api returns json response for all routes', function () {
    $response = $this->getJson('/api/v1/');

    $response->assertHeader('Content-Type', 'application/json');
});

test('api fallback route returns 404 for non-existent routes', function () {
    $response = $this->getJson('/api/v1/non-existent-route');

    $response->assertStatus(404)
        ->assertJsonStructure([
            'message',
        ])
        ->assertJson([
            'message' => 'Route not found',
        ]);
});

test('api fallback route returns json response', function () {
    $response = $this->getJson('/api/v1/non-existent-route');

    $response->assertHeader('Content-Type', 'application/json');
});

test('api routes force json content type header', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'testjson@example.com',
        'password' => 'Password123!@#',
        'password_confirmation' => 'Password123!@#',
    ]);

    $response->assertHeader('Content-Type', 'application/json');
});

test('api routes accept json content type', function () {
    $response = $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ])->getJson('/api/v1/');

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/json');
});
