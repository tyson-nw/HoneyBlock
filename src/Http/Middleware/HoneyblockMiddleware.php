<?php

declare(strict_types=1);

namespace Honeyblock\Honeyblock\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class HoneyblockMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        // 1. Check if IP is whitelisted
        $isWhitelisted = DB::table('honeyblock_whitelist')
            ->where('ip', $ip)
            ->exists();

        if ($isWhitelisted) {
            return $next($request);
        }

        // 2. Check if path matches any trap pattern
        $path = trim($request->path(), '/');
        $traps = config('honeyblock.traps', []);

        $isTrapped = false;
        foreach ($traps as $trap) {
            if ($request->is($trap) || $path === trim((string) $trap, '/')) {
                $isTrapped = true;

                break;
            }
        }

        if ($isTrapped) {
            // 3. Purge requests older than decay threshold
            $decaySeconds = (int) config('honeyblock.decay', 0);

            if ($decaySeconds > 0) {
                DB::table('honeyblock_requests')
                    ->where('ip', $ip)
                    ->where('created_at', '<', now()->subSeconds($decaySeconds))
                    ->delete();
            }

            // 4. Record current request
            DB::table('honeyblock_requests')->insert([
                'ip' => $ip,
                'trap' => $path,
                'forgiven' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 5. Calculate penalty based on total active unforgiven requests
            $requestCount = DB::table('honeyblock_requests')
                ->where('ip', $ip)
                ->where('forgiven', false)
                ->count();

            $baseTimeoutMs = (int) config('honeyblock.base_timeout', 100);
            $delayMs = $baseTimeoutMs * $requestCount;

            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        return $next($request);
    }
}
