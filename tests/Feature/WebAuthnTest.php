<?php

use App\Models\User;
use App\Models\Location;
use App\Models\WebAuthnCredential;
use App\Models\AttendanceLog;

it('allows punching in with valid credential', function () {
    $location = Location::create([
        'name' => 'Office',
        'latitude' => 0,
        'longitude' => 0,
        'radius_meters' => 100,
    ]);

    $user = User::factory()->create(['location_id' => $location->id]);
    $cred = WebAuthnCredential::create([
        'user_id' => $user->id,
        'name' => 'finger',
        'credential_id' => 'valid-cred',
        'public_key' => 'pk',
    ]);

    $response = $this->actingAs($user)->post('/punch-in', [
        'latitude' => 0,
        'longitude' => 0,
        'credential_id' => 'valid-cred',
    ]);

    $response->assertRedirect('/dashboard');
    expect(AttendanceLog::where('user_id', $user->id)->exists())->toBeTrue();
});

it('rejects punching in with invalid credential', function () {
    $location = Location::create([
        'name' => 'Office',
        'latitude' => 0,
        'longitude' => 0,
        'radius_meters' => 100,
    ]);

    $user = User::factory()->create(['location_id' => $location->id]);
    WebAuthnCredential::create([
        'user_id' => $user->id,
        'name' => 'finger',
        'credential_id' => 'valid-cred',
        'public_key' => 'pk',
    ]);

    $response = $this->actingAs($user)->post('/punch-in', [
        'latitude' => 0,
        'longitude' => 0,
        'credential_id' => 'wrong-cred',
    ]);

    $response->assertSessionHas('error');
    expect(AttendanceLog::where('user_id', $user->id)->exists())->toBeFalse();
});

it('rejects punching in without credential', function () {
    $location = Location::create([
        'name' => 'Office',
        'latitude' => 0,
        'longitude' => 0,
        'radius_meters' => 100,
    ]);

    $user = User::factory()->create(['location_id' => $location->id]);
    WebAuthnCredential::create([
        'user_id' => $user->id,
        'name' => 'finger',
        'credential_id' => 'valid-cred',
        'public_key' => 'pk',
    ]);

    $response = $this->actingAs($user)->post('/punch-in', [
        'latitude' => 0,
        'longitude' => 0,
    ]);

    $response->assertSessionHas('error');
    expect(AttendanceLog::where('user_id', $user->id)->exists())->toBeFalse();
});

it('stores public key when registering credential', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/webauthn/register', [
        'name' => 'finger',
        'credential_id' => 'cred-1',
        'public_key' => base64_encode('pk-data'),
    ]);

    $response->assertOk();
    $cred = WebAuthnCredential::first();
    expect($cred->public_key)->toBe(base64_encode('pk-data'));
});
