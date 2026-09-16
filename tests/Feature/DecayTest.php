<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('trapped requests older than decay setting are purged', function () {
    $ip = '192.168.1.205';
    $trappedPath = 'wp-admin';
    $decaySeconds = (int) config('honeyblock.decay', 3600);
    $baseTimeoutMs = (int) config('honeyblock.base_timeout', 100);

    // Initial trapped request
    $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->get("/{$trappedPath}");

    $this->assertDatabaseCount('honeyblock_requests', 1);

    // Fast-forward time past the decay threshold
    $this->travel($decaySeconds + 10)->seconds();

    // Second trapped request after decay
    $start = microtime(true);

    $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->get("/{$trappedPath}");

    $durationMs = (microtime(true) - $start) * 1000;

    // The initial request record was purged, so database count remains 1
    $this->assertDatabaseCount('honeyblock_requests', 1);

    // Delay duration reflects 1 active request (base_timeout * 1) rather than 2
    expect($durationMs)->toBeLessThan($baseTimeoutMs * 2);
});
