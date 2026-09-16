<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('forgiven requests are excluded from timeout multiplier', function () {
    $ip = '192.168.1.210';
    $trappedPath = 'wp-admin';
    $baseTimeoutMs = (int) config('honeyblock.base_timeout', 100);

    // Seed forgiven requests for this IP directly in the database
    DB::table('honeyblock_requests')->insert([
        [
            'ip' => $ip,
            'trap' => 'admin',
            'forgiven' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'ip' => $ip,
            'trap' => 'wp-login.php',
            'forgiven' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    // Send a new trapped request
    $start = microtime(true);

    $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->get("/{$trappedPath}");

    $durationMs = (microtime(true) - $start) * 1000;

    // Database now contains 3 records (2 forgiven + 1 unforgiven)
    $this->assertDatabaseCount('honeyblock_requests', 3);

    // Assert the new request is logged as unforgiven
    $this->assertDatabaseHas('honeyblock_requests', [
        'ip' => $ip,
        'trap' => $trappedPath,
        'forgiven' => false,
    ]);

    // Delay duration should reflect only 1 active unforgiven request, not 3
    expect($durationMs)->toBeLessThan($baseTimeoutMs * 3);
});
