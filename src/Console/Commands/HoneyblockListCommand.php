<?php

declare(strict_types=1);

namespace Honeyblock\Honeyblock\Console\Commands;

use Honeyblock\Honeyblock\Facades\Honeyblock;
use Illuminate\Console\Command;

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
        $blockedIps = Honeyblock::listBlockedIps();

        if (empty($blockedIps)) {
            $this->info(__('No active blocked IPs found.'));

            return self::SUCCESS;
        }

        $this->table([
            __('IP Address'),
            __('Unforgiven Requests'),
        ], $blockedIps);

        return self::SUCCESS;
    }
}