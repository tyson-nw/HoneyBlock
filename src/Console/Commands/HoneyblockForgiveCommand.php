<?php

declare(strict_types=1);

namespace Honeyblock\Honeyblock\Console\Commands;

use Honeyblock\Honeyblock\Facades\Honeyblock;
use Illuminate\Console\Command;

class HoneyblockForgiveCommand extends Command
{
    protected $signature = 'honeyblock:forgive {ip : The IP address to forgive}';

    public function __construct()
    {
        parent::__construct();
        $this->description = __('Remove blocked request records for a specific IP address');
    }

    public function handle(): int
    {
        $ip = (string) $this->argument('ip');

        $count = Honeyblock::forgive($ip);

        if ($count > 0) {
            $this->info("Forgave IP address: {$ip}");
        } else {
            $this->warn("No blocked records found for IP address: {$ip}");
        }

        return self::SUCCESS;
    }
}