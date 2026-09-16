<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('whitelisted ip bypasses trapped route delays and logging', function () {
    $ip = '192.168.1.200';
    $trappedPath = 'wp-admin';
    $baseTimeoutMs = (int) config('honeyblock.base_timeout', 100);

    DB::table('honeyblock_whitelist')->insert([
        'ip' => $ip,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $start = microtime(true);

    $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->get("/{$trappedPath}");

    $durationMs = (microtime(true) - $start) * 1000;

    $this->assertDatabaseMissing('honeyblock_requests', [
        'ip' => $ip,
    ]);

    expect($durationMs)->toBeLessThan($baseTimeoutMs);
});

test('whitelisted ip accesses valid route without penalties', function () {
    $ip = '192.168.1.201';
    $trappedPath = 'admin';
    $baseTimeoutMs = (int) config('honeyblock.base_timeout', 100);

    DB::table('honeyblock_whitelist')->insert([
        'ip' => $ip,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Route::get('/admin', function () {
        return response('Legitimate Admin Dashboard', 200);
    });

    $start = microtime(true);

    $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
        ->get("/{$trappedPath}");

    $durationMs = (microtime(true) - $start) * 1000;

    $response->assertStatus(200);

    $this->assertDatabaseMissing('honeyblock_requests', [
        'ip' => $ip,
    ]);

    expect($durationMs)->toBeLessThan($baseTimeoutMs);
});
