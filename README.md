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

You can install the package via Composer:

```bash
composer require tyson-vanoverhill/honeyblock
```

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="honeyblock"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="honeyblock-config"
```

### Publishing and Running the Migrations

```bash
php artisan vendor:publish --tag="honeyblock-migrations"
php artisan migrate
```

### Publishing the Translations

```bash
php artisan vendor:publish --tag="honeyblock-lang"
```

## Usage

<!-- Add a basic usage example here. -->

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Honeyblock! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Tyson VanOverhill](https://github.com/tyson-vanoverhill)
- [All Contributors](../../contributors)

## License

Honeyblock is open-sourced software licensed under the [MIT license](LICENSE.md).
