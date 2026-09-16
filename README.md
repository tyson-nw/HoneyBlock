<div align="center">
    <h1>Honeyblock</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/tyson-vanoverhill/honeyblock"><img src="https://img.shields.io/packagist/v/tyson-vanoverhill/honeyblock.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/tyson-vanoverhill/honeyblock"><img src="https://img.shields.io/packagist/php-v/tyson-vanoverhill/honeyblock.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/tyson-vanoverhill/honeyblock"><img src="https://badge.laravel.cloud/badge/tyson-vanoverhill/honeyblock?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/tyson-vanoverhill/honeyblock/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/tyson-vanoverhill/honeyblock/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/tyson-vanoverhill/honeyblock"><img src="https://img.shields.io/packagist/dt/tyson-vanoverhill/honeyblock.svg?style=flat-square" alt="Total Downloads"></a>
</p>


## Installation

Install the package via Composer:

```bash
composer config repositories.honeyblock vcs [https://github.com/tyson-nw/HoneyBlock](https://github.com/tyson-nw/HoneyBlock)
composer require tyson-nw/honeyblock
```

Run the database migrations:

```bash
php artisan migrate
```

### Publishing Resources (Optional)

Publish the configuration file:

```bash
php artisan vendor:publish --tag="honeyblock-config"
```

Publish the translation files:

```bash
php artisan vendor:publish --tag="honeyblock-lang"
```

Or publish all package resources at once:

```bash
php artisan vendor:publish --tag="honeyblock"
```


## Usage

### Configuration
- **all_404** If all 404 requests should be treated as traps.
- **traps** List of the beginnings of routes that should be traps.
- **base_timeout** The amount of time to add to the first trapped request
- **timeout_multiplier** The multiplier to increase the amount of time each subsequent request increases the timeout.
- **decay** The amount of time before a trapped request falls off the ip's record.

### Console Commands
- **Forgive <$ip>** Takes the ip and clears all trap records.
- **List** Lists all ips and the count of how many traps they have tripped.
- **Whitelist <$ip>** Whitelists the ip and prevents trap records being created and clears all trap records.  

## Programmatic Usage

Interact with Honeyblock programmatically within your controllers, services, or custom middleware using the `Honeyblock` Facade:

```php
use Honeyblock\Honeyblock\Facades\Honeyblock;
```

### Blocking an IP

Record a blocked IP entry manually. You can optionally pass a custom trap identifier (defaults to `'manual'`):

```php
// Block an IP using the default 'manual' trap
Honeyblock::block('192.168.1.50');

// Block an IP with a custom trap identifier
Honeyblock::block('192.168.1.50', 'suspicious-login-attempts');
```

### Forgiving an IP

Mark all current unforgiven request entries for an IP address as forgiven. Returns the number of updated records:

```php
$count = Honeyblock::forgive('192.168.1.50');
// Returns (int) total records forgiven, e.g., 3
```

### Whitelisting an IP

Add or update an IP address in the whitelist to prevent automated blocks:

```php
Honeyblock::whitelist('10.0.0.1');
```

### Removing an IP from the Whitelist

Remove a previously whitelisted IP address:

```php
$removed = Honeyblock::removeFromWhitelist('10.0.0.1');
// Returns true if the IP was found and removed, false otherwise
```

### Checking Whitelist Status

Determine whether an IP address is currently on the whitelist:

```php
if (Honeyblock::isWhitelisted('10.0.0.1')) {
    // Perform actions for whitelisted users
}
```
Retrieve an array of all currently whitelisted IP addresses:

```php
$whitelistedIps = Honeyblock::listWhitelisted();
// Returns ['10.0.0.1', '10.0.0.2']
```

### Listing Whitelisted IPs

Retrieve a simple array of all currently whitelisted IP addresses:

```php
$whitelistedIps = Honeyblock::listWhitelisted();

// Returns: ['10.0.0.1', '10.0.0.2']
```

### Listing Unforgiven Blocked Requests

Retrieve an array of all active (unforgiven) blocked request entries including their IP, trap type, and creation timestamp:

```php
$blockedRequests = Honeyblock::listBlockedRequests();
/*
Returns:
[
    [
        'ip' => '192.168.1.100',
        'trap' => 'login-trap',
        'created_at' => '2026-09-16 12:00:00',
    ],
]
*/
```

### Listing Blocked IPs and Request Counts

Retrieve an aggregated array of all currently blocked IP addresses along with their unforgiven request count:

```php
$blockedIps = Honeyblock::listBlockedIps();
/*
Returns:
[
    [
        'ip' => '192.168.1.100',
        'count' => 3,
    ],
    [
        'ip' => '192.168.1.101',
        'count' => 1,
    ],
]
*/
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Honeyblock! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started. We are particularly interested in improving our translations.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Tyson VanOverhill](https://github.com/tyson-vanoverhill)
- [All Contributors](../../contributors)

## License

Honeyblock is open-sourced software licensed under the [MIT license](LICENSE.md).
