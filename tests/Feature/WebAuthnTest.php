<?php

use App\Models\User;
use App\Models\Location;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Laragear\WebAuthn\Http\Routes as WebAuthnRoutes;
use App\Models\AttendanceLog;
use Mockery;
use Laragear\WebAuthn\Assertion\Validator\AssertionValidator;

beforeEach(function () {
    WebAuthnRoutes::register();
});

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

    $validator = Mockery::mock(AssertionValidator::class);
    $this->app->instance(AssertionValidator::class, $validator);
    $validation = (object) [
        'credential' => $cred,
        'authenticatorData' => (object) ['counter' => 5],
    ];
    $validator->shouldReceive('send')->once()->andReturnSelf();
    $validator->shouldReceive('thenReturn')->once()->andReturn($validation);

    $response = $this->actingAs($user)->post('/punch-in', [
        'latitude' => 0,
        'longitude' => 0,
    ]);

    $response->assertRedirect('/dashboard');
    expect(AttendanceLog::where('user_id', $user->id)->exists())->toBeTrue();
    expect(WebAuthnCredential::find($cred->id)->counter)->toBe(5);
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

    $validator = Mockery::mock(AssertionValidator::class);
    $this->app->instance(AssertionValidator::class, $validator);
    $validator->shouldReceive('send')->once()->andReturnSelf();
    $validator->shouldReceive('thenReturn')->once()->andThrow(new Exception('invalid'));

    $response = $this->actingAs($user)->post('/punch-in', [
        'latitude' => 0,
        'longitude' => 0,
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

    $validator = Mockery::mock(AssertionValidator::class);
    $this->app->instance(AssertionValidator::class, $validator);
    $validator->shouldReceive('send')->never();

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

it('allows user to delete own credential', function () {
    $user = User::factory()->create();
    $cred = WebAuthnCredential::create([
        'user_id' => $user->id,
        'name' => 'finger',
        'credential_id' => 'cred-1',
        'public_key' => 'pk',
    ]);

    $response = $this->actingAs($user)->delete(route('passkeys.destroy', $cred));

    $response->assertRedirect();
    expect(WebAuthnCredential::count())->toBe(0);
});

it('prevents deleting credential of another user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $cred = WebAuthnCredential::create([
        'user_id' => $other->id,
        'name' => 'finger',
        'credential_id' => 'cred-2',
        'public_key' => 'pk',
    ]);

    $response = $this->actingAs($user)->delete(route('passkeys.destroy', $cred));

    $response->assertForbidden();
    expect(WebAuthnCredential::count())->toBe(1);
});
