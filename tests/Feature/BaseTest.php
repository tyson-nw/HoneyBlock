<?php

declare(strict_types=1);

use Honeyblock\Honeyblock\Honeyblock;

it('resolves the singleton', function () {
    expect(app(Honeyblock::class))->toBeInstanceOf(Honeyblock::class);
});

it('returns the same instance from the container', function () {
    expect(app(Honeyblock::class))->toBe(app(Honeyblock::class));
});

// it('merges the package config', function () {
//     expect(config('honeyblock.all_404'))->toBe('default');
// });

// it('loads the package translations', function () {
//     expect(trans('honeyblock::messages.placeholder'))->toBe('Honeyblock placeholder translation.');
// });

// it('registers the artisan command', function () {
//     $this->artisan('honeyblock:list')
//         ->expectsOutputToContain('Lists all blocked IPs and their request counts.')
//         ->assertSuccessful();
// });
