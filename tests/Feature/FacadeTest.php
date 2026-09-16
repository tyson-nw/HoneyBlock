<?php

declare(strict_types=1);

use Honeyblock\Honeyblock\Facades\Honeyblock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('facade can programmatically block an ip', function () {
    $ip = '192.168.1.99';
    $trap = 'custom-trap';

    $result = Honeyblock::block($ip, $trap);

    expect($result)->toBeTrue();

    $this->assertDatabaseHas('honeyblock_requests', [
        'ip' => $ip,
        'trap' => $trap,
        'forgiven' => false,
    ]);
});

test('facade can forgive active requests for an ip', function () {
    $ip = '192.168.1.100';

    DB::table('honeyblock_requests')->insert([
        ['ip' => $ip, 'trap' => 'admin', 'forgiven' => false, 'created_at' => now(), 'updated_at' => now()],
        ['ip' => $ip, 'trap' => 'login', 'forgiven' => false, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $affected = Honeyblock::forgive($ip);

    expect($affected)->toBe(2);

    $this->assertDatabaseMissing('honeyblock_requests', [
        'ip' => $ip,
        'forgiven' => false,
    ]);
});

test('facade can add an ip to the whitelist', function () {
    $ip = '10.0.0.50';

    $result = Honeyblock::whitelist($ip);

    expect($result)->toBeTrue();

    $this->assertDatabaseHas('honeyblock_whitelist', [
        'ip' => $ip,
    ]);
});

test('facade can check if an ip is whitelisted', function () {
    $ip = '10.0.0.51';

    expect(Honeyblock::isWhitelisted($ip))->toBeFalse();

    DB::table('honeyblock_whitelist')->insert([
        'ip' => $ip,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(Honeyblock::isWhitelisted($ip))->toBeTrue();
});