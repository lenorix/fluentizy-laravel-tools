# fluentizy-laravel-tools

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lenorix/fluentizy-laravel-tools.svg?style=flat-square)](https://packagist.org/packages/lenorix/fluentizy-laravel-tools)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/lenorix/fluentizy-laravel-tools/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/lenorix/fluentizy-laravel-tools/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/lenorix/fluentizy-laravel-tools/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/lenorix/fluentizy-laravel-tools/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/lenorix/fluentizy-laravel-tools.svg?style=flat-square)](https://packagist.org/packages/lenorix/fluentizy-laravel-tools)

It helps you to internationalize and localize your Laravel applications easily.

## Support us

Help us continue developing and maintaining this package by sponsoring us.

## Installation

You can install the package via composer:

```bash
composer require --dev lenorix/fluentizy-laravel-tools
```

That's all! This is enough to get started using `lang:extract` command.

You can publish the config file with:

```bash
php artisan vendor:publish --tag="fluentizy-tools-config"
```

You can publish the translation files with:

```bash
php artisan vendor:publish --tag="fluentizy-tools-translations"
```

## Usage

To extract translations from your codebase, run:

```bash
php artisan lang:extract
```

This command will update your translation files based on `__('...')` usages in your code.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [lenorix](https://github.com/lenorix)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
