<?php

test('login page is available', function () {
    $response = $this->get('/login');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('auth/login'));
});
