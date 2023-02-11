# Banhammer, a ban package for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mchev/banhammer.svg?style=flat-square)](https://packagist.org/packages/mchev/banhammer)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mchev/banhammer/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mchev/banhammer/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mchev/banhammer.svg?style=flat-square)](https://packagist.org/packages/mchev/banhammer)
[![Package for laravel](https://img.shields.io/badge/Package%20for%20Laravel-grey.svg?style=flat-square&logo=laravel&logoColor=white)](https://packagist.org/packages/mchev/banhammer)

Banhammer allows you to ban any Model by key and by IP.

## Compatibility

Banhammer is

## Installation

You can install the package via composer:

```bash
composer require mchev/banhammer
```

Then run the migrations with:

```bash
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="banhammer-config"
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

### Ban / Unban

```php
// Simple ban
$user->ban();

// IP Ban
$user->ban([
	'ip' => $user->ip,
]);

// Full (all attributes are optional)
$user->ban([
	'created_by_type' => 'App\Models\User',
	'created_by_id' => 1,
	'comment' => "You've been evil",
	'ip' => "8.8.8.8",
	'expired_at' => Carbon::now()->addDays(7)
]);

// Shorthand
$user->banUntil('2 days');

// Unban
$user->unban();
```

### Middleware
To prevent banned users from accessing certain parts of your application, simply add the `auth.banned` middleware on the concerned routes.
```php
Route::get('/profile', function () {
    // ...
})->middleware('auth.banned');
```

### Scheduler

### Events

If entity is banned Mchev\Banhammer\Events\ModelWasBanned event is fired.
Is entity is unbanned Mchev\Banhammer\Events\ModelWasUnbanned event is fired.

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
