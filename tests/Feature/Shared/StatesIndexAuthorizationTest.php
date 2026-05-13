<?php

use App\Models\User;

test('authenticated root user can view the states index', function () {
    $user = User::factory()->root()->create();

    $response = $this->actingAs($user)->get(route('shared.states.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('shared/states/index'));
});

test('authenticated non-root user can view the states index', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('shared.states.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('shared/states/index'));
});
