# Laravel Locale Via API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/empuxa/laravel-locale-via-api.svg?style=flat-square)](https://packagist.org/packages/empuxa/laravel-locale-via-api)
[![Tests](https://img.shields.io/github/actions/workflow/status/empuxa/laravel-locale-via-api/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/empuxa/laravel-locale-via-api/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/empuxa/laravel-locale-via-api.svg?style=flat-square)](https://packagist.org/packages/empuxa/laravel-locale-via-api)

![Banner](https://banners.beyondco.de/Laravel%20Locale%20Via%20API.png?theme=light&packageManager=composer+require&packageName=empuxa%2Flaravel-locale-via-api&pattern=architect&style=style_1&description=&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg)

This package lets you share your local translations via API, so that any app can use them. 
To do so, it converts your PHP lang files to JSON.

## Requirements
1. You must provide your lang files within folders, such as `lang/en` or `lang/de`.
2. The lang files must be in PHP format, such as `lang/en/validation.php`.
3. Must secure the API routes and its patterns on your own.

## Installation

This package requires Laravel 9.33+.
You can install the package via composer:

```bash
composer require empuxa/laravel-locale-via-api
```

Afterward, you might want to copy the vendor files:

```bash
php artisan vendor:publish --provider="Empuxa\LocaleViaApi\LocaleViaApiServiceProvider"
```

Remember to also update the config file `config/locale-via-api.php` to your needs!

## Usage
This package provides two controllers to access your translations via API.
You must add them to your `routes/api.php` file manually.

### `Empuxa\LocaleViaApi\Http\Controllers\ListLocalesController`
With this controller, you can list any locale that is available in your app.
For security reasons, it only returns the array from your config and doesn't read the actual files.

```php
Route::get('/locales', ListLocalesController::class);
```

By adding `?flatten=true` to the URL, you can get a flat array of all available locales.
You can also change the default behavior in the config file.

#### Non-flattened response
```json
{
    "data": {
        "api": {
            "error": {
                "401": "Unauthenticated.",
                "403": "Forbidden.",
                "404": "Not Found.",
                "422": "Unprocessable Entity."
            }
        }
    }
}
```

#### Flattened response
```json
{
    "data": {
        "api.error.401": "Unauthenticated.",
        "api.error.403": "Forbidden.",
        "api.error.404": "Not Found.",
        "api.error.422": "Unprocessable Entity."
    }
}
```

### `Empuxa\LocaleViaApi\Http\Controllers\GetLocaleController`
This controller returns the contents of a locale directory as JSON.
If the directory does not exist, it will return an error 404.

```php
Route::get('/locales/{locale}', GetLocaleController::class);
```

## Security
Since this package reads your lang files (and could theoretically read any other files as well), it is important to protect your API routes.
**Make sure that you only share the locales that you want to share.**
Add route pattern to do so.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Marco Raddatz](https://github.com/marcoraddatz)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
