<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('list command displays ips and unforgiven counts', function () {
    DB::table('honeyblock_requests')->insert([
        ['ip' => '192.168.1.10', 'trap' => 'admin'],
        ['ip' => '192.168.1.10', 'trap' => 'wp-admin'],
        ['ip' => '192.168.1.20', 'trap' => 'phpmyadmin'],
    ]);

    $this->artisan('honeyblock:list')
        ->expectsTable(
            [__('IP Address'), __('Unforgiven Requests')],
            [
                ['192.168.1.10', '2'],
                ['192.168.1.20', '1'],
            ],
        )
        ->assertExitCode(0);
});

test('forgive command deletes ip records from database', function () {
    $ip = '192.168.1.50';

    DB::table('honeyblock_requests')->insert([
        ['ip' => $ip, 'trap' => 'admin', 'created_at' => now(), 'updated_at' => now()],
        ['ip' => $ip, 'trap' => 'wp-admin', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->assertDatabaseCount('honeyblock_requests', 2);

    $this->artisan('honeyblock:forgive', ['ip' => $ip])
        ->expectsOutput("Forgave IP address: {$ip}")
        ->assertExitCode(0);

    $this->assertDatabaseMissing('honeyblock_requests', [
        'ip' => $ip,
    ]);

    $this->assertDatabaseCount('honeyblock_requests', 0);
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
