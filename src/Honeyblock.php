<?php

declare(strict_types=1);

namespace Honeyblock\Honeyblock;

use Illuminate\Support\Facades\DB;

class Honeyblock
{
    public function block(string $ip, string $trap = 'manual'): bool
    {
        return DB::table('honeyblock_requests')->insert([
            'ip' => $ip,
            'trap' => $trap,
            'forgiven' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    public function forgive(string $ip): int
    {
        return DB::table('honeyblock_requests')
            ->where('ip', $ip)
            ->where('forgiven', false)
            ->update([
                'forgiven' => true,
                'updated_at' => now(),
            ]);
    }

    public function whitelist(string $ip): bool
    {
        return DB::table('honeyblock_whitelist')->updateOrInsert(
            ['ip' => $ip],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    public function isWhitelisted(string $ip): bool
    {
        return DB::table('honeyblock_whitelist')
            ->where('ip', $ip)
            ->exists();
    }
}