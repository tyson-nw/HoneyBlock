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
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function blockedCount(string $ip): int
    {
        if ($this->isWhitelisted($ip)) {
            return 0;
        }

        return DB::table('honeyblock_requests')
        ->where('ip', $ip)
        ->count();
    }

    public function forgive(string $ip): int
    {
        return DB::table('honeyblock_requests')
            ->where('ip', $ip)
            ->delete();
    }

    public function pruneExpired(): int
    {
        $decay = (int) config('honeyblock.decay');

        if ($decay <= 0) {
            return 0;
        }

        $cutoff = now()->subSeconds((int) $decay);

        return DB::table('honeyblock_requests')
            ->where('created_at', '<', $cutoff)
            ->delete();
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
            ->get(['ip'])
            ->groupBy('ip')
            ->map(static fn (\Illuminate\Support\Collection $items, string $ip): array => [
                'ip' => $ip,
                'count' => $items->count(),
            ])
            ->values()
            ->all();
    }
}