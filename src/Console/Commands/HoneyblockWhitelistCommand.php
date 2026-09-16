<?php

declare(strict_types=1);

namespace Honeyblock\Honeyblock\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use stdClass;

class HoneyblockWhitelistCommand extends Command
{
    public function __construct()
    {
        $this->signature = 'honeyblock:whitelist {ip? : '.__('Optional IP address to add to whitelist').'}';
        parent::__construct();
        $this->description = __('Add an IP address to the whitelist or list all whitelisted IPs');
    }

    public function handle(): int
    {
        $rawIp = $this->argument('ip');
        $ip = is_string($rawIp) ? trim($rawIp) : '';

        if ($ip !== '') {
            DB::table('honeyblock_whitelist')->updateOrInsert(
                ['ip' => $ip],
                ['created_at' => now(), 'updated_at' => now()],
            );

            $this->info(__('IP address :ip has been whitelisted.', ['ip' => $ip]));

            return self::SUCCESS;
        }

        $whitelisted = DB::table('honeyblock_whitelist')
            ->select('ip', 'created_at')
            ->get();

        if ($whitelisted->isEmpty()) {
            $this->info(__('No whitelisted IPs found.'));

            return self::SUCCESS;
        }

        $rows = $whitelisted->map(fn (stdClass $record): array => [
            'ip' => (string) $record->ip,
            'whitelisted_at' => (string) $record->created_at,
        ])->toArray();

        $this->table([
            __('Whitelisted IP Address'),
            __('Added At'),
        ], $rows);

        return self::SUCCESS;
    }
}
