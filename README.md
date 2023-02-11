# Ban System for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mchev/bans-for-laravel.svg?style=flat-square)](https://packagist.org/packages/mchev/bans-for-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mchev/bans-for-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mchev/bans-for-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/mchev/bans-for-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/mchev/bans-for-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mchev/bans-for-laravel.svg?style=flat-square)](https://packagist.org/packages/mchev/bans-for-laravel)

Bans for Laravel allows you to ban any Model by key and by IP.

## Installation

You can install the package via composer:

```bash
composer require mchev/bans-for-laravel
```

Then run the migrations with:

```bash
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="bans-for-laravel-config"
```

## Usage

To make a model bannable, add the `Mchev\Banhammer\Traits\Bannable` trait to the model:
```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Mchev\Banhammer\Traits\Bannable;

class User extends Authenticatable
{
    use Bannable;
}
```

```php
$bansForLaravel = new Mchev\Banhammer();

// Simple ban
$user->ban();

// IP Ban
$user->ban([
	'comment' => "You've been evil",
	'ip' => "8.8.8.8",
]);

// Ban with expiration date
$team->ban([
	'expired_at' => Carbon::now()->addDays(7);
])


```

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

- [mchev](https://github.com/mchev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
