<?php

declare(strict_types=1);

use Honeyblock\Honeyblock\HoneyblockServiceProvider;

test('service provider skips console publishing when not running in console', function () {
    $app = Mockery::mock($this->app)->makePartial();
    $app->shouldReceive('runningInConsole')->andReturn(false);

    $provider = new HoneyblockServiceProvider($app);
    $provider->boot();

    expect($app->runningInConsole())->toBeFalse();
});
