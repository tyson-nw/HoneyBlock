<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('list command displays ips and unforgiven counts', function () {
    DB::table('honeyblock_requests')->insert([
        ['ip' => '192.168.1.10', 'trap' => 'admin', 'forgiven' => false, 'created_at' => now(), 'updated_at' => now()],
        ['ip' => '192.168.1.10', 'trap' => 'wp-admin', 'forgiven' => false, 'created_at' => now(), 'updated_at' => now()],
        ['ip' => '192.168.1.10', 'trap' => 'manager', 'forgiven' => true, 'created_at' => now(), 'updated_at' => now()],
        ['ip' => '192.168.1.20', 'trap' => 'phpmyadmin', 'forgiven' => false, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->artisan('honeyblock:list')
        ->expectsTable(
            [__('IP Address'), __('Unforgiven Requests')],
            [
                ['192.168.1.10', '2'],
                ['192.168.1.20', '1'],
            ]
        )
        ->assertExitCode(0);
});

test('forgive command updates ip records to forgiven', function () {
    $ip = '192.168.1.50';

    DB::table('honeyblock_requests')->insert([
        ['ip' => $ip, 'trap' => 'admin', 'forgiven' => false, 'created_at' => now(), 'updated_at' => now()],
        ['ip' => $ip, 'trap' => 'wp-admin', 'forgiven' => false, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->artisan('honeyblock:forgive', ['ip' => $ip])
        ->assertExitCode(0);

    $this->assertDatabaseHas('honeyblock_requests', [
        'ip' => $ip,
        'forgiven' => true,
    ]);

    $this->assertDatabaseMissing('honeyblock_requests', [
        'ip' => $ip,
        'forgiven' => false,
    ]);
});

test('whitelist command adds ip or lists whitelisted ips', function () {
    $ip = '10.0.0.1';

    // 1. Add IP to whitelist when passing the argument
    $this->artisan('honeyblock:whitelist', ['ip' => $ip])
        ->assertExitCode(0);

    $this->assertDatabaseHas('honeyblock_whitelist', [
        'ip' => $ip,
    ]);

    // 2. List whitelisted IPs when running without an argument
    $this->artisan('honeyblock:whitelist')
        ->expectsOutputToContain($ip)
        ->assertExitCode(0);
});