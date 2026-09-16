<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('trapped route logs ip and applies base timeout', function () {
    $ip = '192.168.1.100';
    $trappedPath = 'wp-admin';
    $baseTimeoutMs = config('honeyblock.base_timeout', 100);

    $start = microtime(true);

    $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->get("/{$trappedPath}");

    $durationMs = (microtime(true) - $start) * 1000;

    $this->assertDatabaseHas('honeyblock_requests', [
        'ip' => $ip,
        'trap' => $trappedPath,
        'forgiven' => false,
    ]);

    expect($durationMs)->toBeGreaterThanOrEqual($baseTimeoutMs);
});

test('repeated trapped requests incrementally increase timeout', function () {
    $ip = '192.168.1.101';
    $trappedPath = 'admin';
    $baseTimeoutMs = config('honeyblock.base_timeout', 100);

    // Request 1 (1x multiplier)
    $start1 = microtime(true);
    $this->withServerVariables(['REMOTE_ADDR' => $ip])->get("/{$trappedPath}");
    $duration1 = (microtime(true) - $start1) * 1000;

    // Request 2 (2x multiplier)
    $start2 = microtime(true);
    $this->withServerVariables(['REMOTE_ADDR' => $ip])->get("/{$trappedPath}");
    $duration2 = (microtime(true) - $start2) * 1000;

    // Request 3 (3x multiplier)
    $start3 = microtime(true);
    $this->withServerVariables(['REMOTE_ADDR' => $ip])->get("/{$trappedPath}");
    $duration3 = (microtime(true) - $start3) * 1000;

    $this->assertDatabaseCount('honeyblock_requests', 3);

    expect($duration1)->toBeGreaterThanOrEqual($baseTimeoutMs * 1)
        ->and($duration2)->toBeGreaterThanOrEqual($baseTimeoutMs * 2)
        ->and($duration3)->toBeGreaterThanOrEqual($baseTimeoutMs * 3);
});

test('valid route matching trap path triggers trap behavior', function () {
    $ip = '192.168.1.102';
    $trappedPath = 'admin';

    // Register a valid application route matching a trapped route pattern
    Route::get('/admin', function () {
        return response('Legitimate Admin Dashboard', 200);
    });

    $start = microtime(true);

    $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->get("/{$trappedPath}");

    $durationMs = (microtime(true) - $start) * 1000;

    $this->assertDatabaseHas('honeyblock_requests', [
        'ip' => $ip,
        'trap' => $trappedPath,
    ]);

    $response->assertStatus(200);
    expect($durationMs)->toBeGreaterThanOrEqual(config('honeyblock.base_timeout', 100));
});
