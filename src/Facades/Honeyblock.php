<?php

declare(strict_types=1);

namespace Honeyblock\Honeyblock\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool block(string $ip, string $trap = 'manual')
 * @method static int forgive(string $ip)
 * @method static bool whitelist(string $ip)
 * @method static bool isWhitelisted(string $ip)
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
