<?php

use App\Models\User;

it('dashboard view includes challenge', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
    expect($response->viewData('challenge'))->not->toBeNull();
});
