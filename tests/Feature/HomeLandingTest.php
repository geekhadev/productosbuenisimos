<?php

use Laravel\Fortify\Features;

test('home renders the landing page', function () {
    $response = $this->get(route('home'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('landing/index')
        ->has('canRegister')
        ->where('canRegister', Features::enabled(Features::registration()))
    );
});
