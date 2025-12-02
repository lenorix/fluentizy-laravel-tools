# fluentizy-laravel-tools

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lenorix/fluentizy-laravel-tools.svg?style=flat-square)](https://packagist.org/packages/lenorix/fluentizy-laravel-tools)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/lenorix/fluentizy-laravel-tools/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/lenorix/fluentizy-laravel-tools/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/lenorix/fluentizy-laravel-tools/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/lenorix/fluentizy-laravel-tools/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/lenorix/fluentizy-laravel-tools.svg?style=flat-square)](https://packagist.org/packages/lenorix/fluentizy-laravel-tools)

Extract all translation strings from your codebase and manage your language files effortlessly.

The i18n & l10n will be easier than ever with this package!

## Support us

Help us continue developing and maintaining this package by sponsoring us.

Also, using [fluentizy](https://fluentizy.lenorix.com) is a great way to support the project!

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
php artisan lang:extract es
```

This command will update your translation file based on `__('...')` usages in your code.

If there are `lang/*.json` files, to update all these files, run:

```bash
php artisan lang:extract
```

Behavior:
- Strings that are already translated will not be modified.
- If a string is missing in the translation file, it will be added with an empty value.
- If a string exists in the translation file but is not found in the codebase, it will be removed.

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
