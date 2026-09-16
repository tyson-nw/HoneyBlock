<?php

use Honeyblock\Honeyblock\Facades\Honeyblock;
use Honeyblock\Honeyblock\Http\Middleware\HoneyblockMiddleware;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    // config(['honeyblock.base_timeout' => 0]);


    Route::get('/valid-page', fn () => 'OK');
    Route::get('/wp-admin/login', fn () => 'Trap Page');
});

test('allows unblocked requests', function () {
    $this->get('/valid-page')
        ->assertStatus(200)
        ->assertSee('OK');
});

test('blocks ip when hitting a trap path', function () {
    $ip = '192.168.1.100';
    config([
        'honeyblock.traps' => ['wp-admin'],
        'honeyblock.all_404' => false,
    ]);

    $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->get('/wp-admin/login');

    expect(Honeyblock::blockedCount($ip))->toBe(1);
});

test('blocks ip on 404 responses when all_404 configuration is enabled', function () {
    $ip = '192.168.1.101';
    config(['honeyblock.all_404' => true]);

    $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->get('/non-existent-page')
        ->assertStatus(404);

    expect(Honeyblock::blockedCount($ip))->toBe(1);
});

test('does not block ip on 404 responses when all_404 configuration is disabled', function () {
    $ip = '192.168.1.102';
    config(['honeyblock.all_404' => false]);

    $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->get('/non-existent-page')
        ->assertStatus(404);

    expect(Honeyblock::blockedCount($ip))->toBe(0);
});

test('allows requests if blocked ip is whitelisted', function () {
    $ip = '192.168.1.103';

    Honeyblock::block($ip);
    Honeyblock::whitelist($ip);

    $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->get('/valid-page')
        ->assertStatus(200);

    expect(Honeyblock::blockedCount($ip))->toBe(0);
});

test('applies progressive tarpit delay to blocked ips', function () {
    $ip = '192.168.1.200';

    config([
        'honeyblock.traps' => [],
        'honeyblock.base_timeout' => 1, // 1s delay
        'honeyblock.timeout_multiplier' => 1,
    ]);

    // Pre-populate 2 block records
    Honeyblock::block($ip, 'manual');
    Honeyblock::block($ip, 'manual');

    $start = microtime(true);

    $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->get('/valid-page');

    $duration = microtime(true) - $start;

    // Expecting 2 blocks * 0.05s = ~0.10s delay (allowing small sub-millisecond execution delta)
    expect($duration)->toBeGreaterThanOrEqual(1.95);
});