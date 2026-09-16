<?php

declare(strict_types=1);

namespace Honeyblock\Honeyblock;

use Honeyblock\Honeyblock\Console\Commands\HoneyblockForgiveCommand;
use Honeyblock\Honeyblock\Console\Commands\HoneyblockListCommand;
use Honeyblock\Honeyblock\Console\Commands\HoneyblockWhitelistCommand;

use Honeyblock\Honeyblock\Http\Middleware\HoneyblockMiddleware;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class HoneyblockServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/honeyblock.php', 'honeyblock');

        $this->app->singleton(Honeyblock::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadJsonTranslationsFrom(__DIR__.'/../lang');

        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(HoneyblockMiddleware::class);

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/honeyblock.php' => config_path('honeyblock.php'),
        ], ['honeyblock', 'honeyblock-config']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/honeyblock'),
        ], ['honeyblock', 'honeyblock-lang']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['honeyblock', 'honeyblock-migrations']);

        $this->commands([
            HoneyblockListCommand::class,
            HoneyblockForgiveCommand::class,
            HoneyblockWhitelistCommand::class,
        ]);
    }
}
