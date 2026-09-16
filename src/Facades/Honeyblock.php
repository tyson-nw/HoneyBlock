<?php

declare(strict_types=1);

namespace Honeyblock\Honeyblock\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool block(string $ip, string $trap = 'manual')
 * @method static int forgive(string $ip)
 * @method static int isBlocked(string $ip)
 * @method static bool whitelist(string $ip)
 * @method static bool removeFromWhitelist(string $ip)
 * @method static bool isWhitelisted(string $ip)
 * @method static array<int, string> listWhitelisted()
 * @method static array<int, array{ip: string, trap: string, created_at: string}> listBlockedRequests()
 * @method static array<int, array{ip: string, count: int}> listBlockedIps()
 *
 * @see \Honeyblock\Honeyblock\Honeyblock
 */
class Honeyblock extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Honeyblock\Honeyblock\Honeyblock::class;
    }
}
