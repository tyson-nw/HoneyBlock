<?php

declare(strict_types=1);

namespace Honeyblock\Honeyblock\Http\Middleware;

use Closure;
use Honeyblock\Honeyblock\Honeyblock;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class HoneyblockMiddleware
{
    protected Honeyblock $honeyblock;

    public function __construct(?Honeyblock $honeyblock = null)
    {
        $this->honeyblock = $honeyblock ?? app(Honeyblock::class);
    }

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        $trapped = false;

        if (! $ip) {
            return $next($request);
        }

        // 1. Bypass all checks if the IP is whitelisted
        if ($this->honeyblock->isWhitelisted($ip)) {
            return $next($request);
        }

        // 2. Prune records older than decay threshold
        $this->honeyblock->pruneExpired();

        // 3. Tarpit delay based on total accumulated blocks for this IP
        $blockedCount = $this->honeyblock->blockedCount($ip);

        if ($blockedCount > 0) {
            $baseTimeout = (float) config('honeyblock.base_timeout', 1);
            $multiplier = (float) config('honeyblock.timeout_multiplier', 1.0);
            $sleepSeconds = (int) round($blockedCount * $baseTimeout * $multiplier);

            // print ($sleepSeconds);
            if ($sleepSeconds > 0) {
                sleep($sleepSeconds);
            }
        }

        // 4. Check path against trap routes
        $path = ltrim($request->path(), '/');
        $traps = (array) config('honeyblock.traps', []);

        foreach ($traps as $trap) {
            $normalizedTrap = ltrim((string) $trap, '/');

            if ($normalizedTrap !== '' && Str::startsWith($path, $normalizedTrap)) {
                $this->honeyblock->block($ip, "trap:{$normalizedTrap}");
                $trapped = true;

                break;
            }
        }

        // 5. Process the request
        $response = $next($request);

        // 6. Block IP if 404 trapping is enabled and response is a 404
        if (! $trapped && config('honeyblock.all_404', false) && $response->getStatusCode() === 404) {
            $this->honeyblock->block($ip, '404');
        }

        return $response;
    }
}
