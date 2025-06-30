<?php

use App\Models\User;
use App\Models\Location;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Laragear\WebAuthn\Http\Routes as WebAuthnRoutes;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    WebAuthnRoutes::register();
});

it('rejects punch in when outside allowed location', function () {
    Storage::fake('public');

    $location = Location::create([
        'name' => 'HQ',
        'latitude' => 0,
        'longitude' => 0,
        'radius_meters' => 100,
    ]);

    $user = User::factory()->create(['location_id' => $location->id]);
    WebAuthnCredential::create([
        'user_id' => $user->id,
        'name' => 'finger',
        'credential_id' => 'cred-1',
        'public_key' => 'pk',
    ]);

    $response = $this->actingAs($user)
        ->from('/dashboard')
        ->post('/punch-in', [
            'latitude' => 1,
            'longitude' => 1,
            'credential_id' => 'cred-1',
        ]);

    $response->assertSessionHas('error');
    expect(AttendanceLog::count())->toBe(0);
});

it('stores selfie and device details on successful punch in', function () {
    Storage::fake('public');

    $location = Location::create([
        'name' => 'HQ',
        'latitude' => 0,
        'longitude' => 0,
        'radius_meters' => 100,
    ]);

    $user = User::factory()->create(['location_id' => $location->id]);
    WebAuthnCredential::create([
        'user_id' => $user->id,
        'name' => 'finger',
        'credential_id' => 'cred-1',
        'public_key' => 'pk',
    ]);

    $base64 = 'data:image/png;base64,' . base64_encode('fake');

    $response = $this->actingAs($user)
        ->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)')
        ->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
        ->post('/punch-in', [
            'latitude' => 0,
            'longitude' => 0,
            'credential_id' => 'cred-1',
            'selfie_image' => $base64,
        ]);

    $response->assertRedirect('/dashboard');
    $log = AttendanceLog::first();
    expect($log)->not->toBeNull();
    expect($log->punch_in_ip_address)->toBe('10.0.0.1');
    expect($log->punch_in_user_agent)->toContain('Windows');
    expect($log->punch_in_selfie_path)->not->toBeNull();
    Storage::disk('public')->assertExists($log->punch_in_selfie_path);
});
