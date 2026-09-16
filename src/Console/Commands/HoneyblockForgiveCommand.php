<?php

declare(strict_types=1);

namespace Honeyblock\Honeyblock\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HoneyblockForgiveCommand extends Command
{
    public function __construct()
    {
        $this->signature = 'honeyblock:forgive {ip : '.__('The IP address to forgive').'}';
        parent::__construct();
        $this->description = __('Mark all current requests for a given IP address as forgiven');
    }

    public function handle(): int
    {
        $rawIp = $this->argument('ip');
        $ip = is_string($rawIp) ? $rawIp : '';

        $affected = DB::table('honeyblock_requests')
            ->where('ip', $ip)
            ->where('forgiven', false)
            ->update([
                'forgiven' => true,
                'updated_at' => now(),
            ]);

        $this->info(__('Forgave :count active request(s) for IP: :ip', [
            'count' => $affected,
            'ip' => $ip,
        ]));

        return self::SUCCESS;
    }
}