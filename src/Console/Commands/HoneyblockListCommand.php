<?php

declare(strict_types=1);

namespace Honeyblock\Honeyblock\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HoneyblockListCommand extends Command
{
    protected $signature = 'honeyblock:list';

    public function __construct()
    {
        parent::__construct();
        $this->description = __('List all blocked IP addresses and their unforgiven request counts');
    }

    public function handle(): int
    {
        $counts = DB::table('honeyblock_requests')
            ->where('forgiven', false)
            ->pluck('ip')
            ->countBy();

        if ($counts->isEmpty()) {
            $this->info(__('No active blocked IPs found.'));

            return self::SUCCESS;
        }

        $rows = $counts->map(fn (int $count, string $ip) => [
            'ip' => $ip,
            'count' => (string) $count,
        ])->values()->toArray();

        $this->table([
            __('IP Address'),
            __('Unforgiven Requests'),
        ], $rows);

        return self::SUCCESS;
    }
}