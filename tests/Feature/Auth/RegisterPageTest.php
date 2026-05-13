<?php

test('register routes are not registered', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});

test('register store route is not registered', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertNotFound();
});
