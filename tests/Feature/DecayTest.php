<?php

use Honeyblock\Honeyblock\Facades\Honeyblock;
use Honeyblock\Honeyblock\Http\Middleware\HoneyblockMiddleware;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(HoneyblockMiddleware::class)->group(function () {
        Route::get('/wp-admin', fn () => 'Trap Page');
    });
});

test('trapped requests older than decay setting are purged', function () {
    $ip = '192.168.1.205';

    // Set decay threshold to 30 seconds
    config(['honeyblock.decay' => 30]);

    // Insert an expired record created 60 seconds ago (older than 30s threshold)
    DB::table('honeyblock_requests')->insert([
        'ip' => $ip,
        'trap' => 'expired-trap',
        'created_at' => now()->subSeconds(60),
        'updated_at' => now()->subSeconds(60),
    ]);

    // Insert an active record created right now
    DB::table('honeyblock_requests')->insert([
        'ip' => $ip,
        'trap' => 'active-trap',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->assertDatabaseCount('honeyblock_requests', 2);

    // Execute the purge
    $pruned = Honeyblock::pruneExpired();

    // Verify 1 record was pruned and only the active record remains
    expect($pruned)->toBe(1);
    $this->assertDatabaseCount('honeyblock_requests', 1);
    $this->assertDatabaseHas('honeyblock_requests', ['trap' => 'active-trap']);
    $this->assertDatabaseMissing('honeyblock_requests', ['trap' => 'expired-trap']);
});