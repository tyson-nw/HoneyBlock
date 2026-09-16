<?php

declare(strict_types=1);

use Honeyblock\Honeyblock\Facades\Honeyblock;
use Honeyblock\Honeyblock\Http\Middleware\HoneyblockMiddleware;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(HoneyblockMiddleware::class)->group(function () {
        Route::get('/valid-page', fn () => 'OK');
        Route::get('/wp-admin', fn () => 'Trap Page');
    });
});

test('whitelisted ip accesses valid route without penalties', function () {
    $ip = '10.0.0.1';

    // Pre-block the IP to verify that whitelisting bypasses tarpit penalties
    Honeyblock::block($ip);
    Honeyblock::whitelist($ip);

    config(['honeyblock.base_timeout' => 1.0]); // 1 second delay if penalty applied

    $start = microtime(true);

    $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->get('/valid-page')
        ->assertStatus(200);

    $durationSeconds = microtime(true) - $start;

    // Execution should be nearly instantaneous (well below the 1.0s penalty)
    expect($durationSeconds)->toBeLessThan(1.0);
});

test('whitelisted ip bypasses trapped route delays and logging', function () {
    $ip = '10.0.0.2';

    Honeyblock::whitelist($ip);
    config(['honeyblock.traps' => ['wp-admin']]);
    config(['honeyblock.base_timeout' => 1.0]);

    $start = microtime(true);

    $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->get('/wp-admin')
        ->assertStatus(200);

    $durationSeconds = microtime(true) - $start;

    $this->assertDatabaseMissing('honeyblock_requests', [
        'ip' => $ip,
    ]);

    expect($durationSeconds)->toBeLessThan(1.0);
});
