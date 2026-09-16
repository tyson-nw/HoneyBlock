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

test('facade can remove an ip address from the whitelist', function () {
    $ip = '10.0.0.1';

    Honeyblock::whitelist($ip);
    expect(Honeyblock::isWhitelisted($ip))->toBeTrue();

    $removed = Honeyblock::removeFromWhitelist($ip);

    expect($removed)->toBeTrue();
    expect(Honeyblock::isWhitelisted($ip))->toBeFalse();
});

test('facade can list all whitelisted ips', function () {
    expect(Honeyblock::listWhitelisted())->toBeArray()->toBeEmpty();

    Honeyblock::whitelist('10.0.0.1');
    Honeyblock::whitelist('10.0.0.2');

    $whitelisted = Honeyblock::listWhitelisted();

    expect($whitelisted)
        ->toBeArray()
        ->toHaveCount(2)
        ->toContain('10.0.0.1', '10.0.0.2');
});

test('facade can list all unforgiven blocked requests', function () {
    expect(Honeyblock::listBlockedRequests())->toBeArray()->toBeEmpty();

    Honeyblock::block('192.168.1.100', 'login-trap');
    Honeyblock::block('192.168.1.101', 'admin-trap');

    $requests = Honeyblock::listBlockedRequests();

    expect($requests)->toHaveCount(2);
    expect($requests[0])->toHaveKeys(['ip', 'trap', 'created_at']);
    expect($requests[0]['ip'])->toBe('192.168.1.100');
    expect($requests[0]['trap'])->toBe('login-trap');
});

test('facade can list all blocked ips with unforgiven request counts', function () {
    expect(Honeyblock::listBlockedIps())->toBeArray()->toBeEmpty();

    Honeyblock::block('192.168.1.100', 'trap-a');
    Honeyblock::block('192.168.1.100', 'trap-b');
    Honeyblock::block('192.168.1.101', 'trap-a');

    $blockedIps = Honeyblock::listBlockedIps();

    expect($blockedIps)->toHaveCount(2);
    expect($blockedIps[0])->toBe([
        'ip' => '192.168.1.100',
        'count' => 2,
    ]);
    expect($blockedIps[1])->toBe([
        'ip' => '192.168.1.101',
        'count' => 1,
    ]);
});