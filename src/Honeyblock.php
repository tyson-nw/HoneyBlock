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

    public function removeFromWhitelist(string $ip): bool
    {
        return DB::table('honeyblock_whitelist')
            ->where('ip', $ip)
            ->delete() > 0;
    }
    
    /**
     * @return array<int, string>
     */
    public function listWhitelisted(): array
    {
        /** @var array<int, string> */
        return DB::table('honeyblock_whitelist')
            ->pluck('ip')
            ->all();
    }

    public function listBlockedRequests(): array
    {
        /** @var array<int, array{ip: string, trap: string, created_at: string}> */
        return DB::table('honeyblock_requests')
            ->where('forgiven', false)
            ->select(['ip', 'trap', 'created_at'])
            ->get()
            ->map(static fn (object $item): array => [
                'ip' => (string) $item->ip,
                'trap' => (string) $item->trap,
                'created_at' => (string) $item->created_at,
            ])
            ->all();
    }

    public function listBlockedIps(): array
    {
        /** @var array<int, array{ip: string, count: int}> */
        return DB::table('honeyblock_requests')
            ->where('forgiven', false)
            ->select('ip', DB::raw('count(*) as aggregate_count'))
            ->groupBy('ip')
            ->get()
            ->map(static fn (object $item): array => [
                'ip' => (string) $item->ip,
                'count' => (int) $item->aggregate_count,
            ])
            ->all();
    }
}