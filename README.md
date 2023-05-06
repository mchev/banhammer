# Banhammer, a Model and IP ban package for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mchev/banhammer.svg?style=flat-square)](https://packagist.org/packages/mchev/banhammer)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mchev/banhammer/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mchev/banhammer/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mchev/banhammer.svg?style=flat-square)](https://packagist.org/packages/mchev/banhammer)
[![Package for laravel](https://img.shields.io/badge/Package%20for%20Laravel-grey.svg?style=flat-square&logo=laravel&logoColor=white)](https://packagist.org/packages/mchev/banhammer)

Banhammer for Laravel offers a very simple way to ban any Model by ID and by IP. It also allows to block requests by IP addresses.

Banned models can have an expiration date and will be automatically unbanned using the Scheduler.

## Version Compatibility

 Laravel        | Banhammer
:---------------------|:----------
 ^9.0                 | 1.x.x
 ^10.0                | 1.x.x

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

It is possible to define the table name and the fallback_url in the `config/ban.php` file.

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
> You can add the Bannable trait on as many models as you want (Team, Group, User, etc.).

### Ban / Unban

Simple ban
```php
$user->ban();
```

> Without the expired_at attribute specified, the user will be banned forever.

IP Ban
```php
$user->ban([
	'ip' => $user->ip,
]);
```

Full 
> All attributes are optional
```php
$model->ban([
	'created_by_type' => 'App\Models\User',
	'created_by_id' => 1,
	'comment' => "You've been evil",
	'ip' => "8.8.8.8",
	'expired_at' => Carbon::now()->addDays(7),
	'metas' => [
		'route' => request()->route()->getName(),
		'user_agent' => request()->header('user-agent')
	]
]);
```

Shorthand
```php
$user->banUntil('2 days');
```

Check if model is banned. 
> You can create custom middlewares using these methods.
```php
$model->isBanned();
$model->isNotBanned();
```

List model bans
```php
// All model bans
$bans = $model->bans()->get();

// Expired bans
$expired = $model->bans()->expired()->get();

// Not expired and permanent bans
$notExpired = $model->bans()->notExpired()->get();
```

Filters
```php
$bannedTeams = Team::banned()->get(); 
$notBannedTeams = Team::notBanned()->get();
```

Unban
```php
$user->unban();
```

### IP

Ban IPs
```php
use Mchev\Banhammer\IP;

IP::ban("8.8.8.8");
IP::ban(["8.8.8.8", "4.4.4.4"]);
```

Unban IPs
```php
use Mchev\Banhammer\IP;

IP::unban("8.8.8.8");
IP::unban(["8.8.8.8", "4.4.4.4"]);
```

List all banned IPs
```php
use Mchev\Banhammer\IP;

$ips = IP::banned()->get(); // Collection
$ips = IP::banned()->pluck('ip')->toArray(); // Array
```

### Metas

Ban IP with metas
```php
use Mchev\Banhammer\IP;

IP::ban("8.8.8.8", [
	'route' => request()->route()->getName(),
	'user_agent' => request()->header('user-agent')
]);
```

Metas usage
```php
$ban->setMeta('username', 'Jane');
$ban->getMeta('username'); // Jane
$ban->hasMeta('username'); // boolean
$ban->forgetMeta('username');
```

Filtering by Meta
```php
IP::banned()->whereMeta('username', 'Jane')->get();
// OR
$users->bans()->whereMeta('username', 'Jane')->get();
// OR
$users->whereBansMeta('username', 'Jane')->get();
```

### Middleware
To prevent banned users from accessing certain parts of your application, simply add the `auth.banned` middleware on the concerned routes.
```php
Route::middleware(['auth.banned'])->group(function () {
    // ...
});
```

To prevent banned ips from accessing certain parts of your application, simply add the `ip.banned` middleware on the concerned routes.
```php
Route::middleware(['ip.banned'])->group(function () {
    // ...
});
```

To block and logout banned Users or IP, add the `logout.banned` middleware:
```php
Route::middleware(['logout.banned'])->group(function () {
    // ...
});
```

> If you use the `logout.banned` middleware, it is not necessary to cumulate the other middlewares.

> If you want to block IPs on every HTTP request of your application, list `Mchev\Banhammer\Middleware\IPBanned` in the `$middleware` property of your `app/Http/Kernel.php` class.

### Scheduler

> ⚠ IMPORTANT

In order to be able to automatically delete expired bans, you must have a cron job set up on your server to run the Laravel Scheduled Jobs

> [Running the scheduler](https://laravel.com/docs/9.x/scheduling#running-the-scheduler)

> [Configure Scheduler on Forge](https://forge.laravel.com/docs/1.0/resources/scheduler.html#laravel-scheduled-jobs)

### Events

If entity is banned `Mchev\Banhammer\Events\ModelWasBanned` event is fired.

Is entity is unbanned `Mchev\Banhammer\Events\ModelWasUnbanned` event is fired.

### MISC

To manually unban expired bans :
```php
use Mchev\Banhammer\Banhammer;

Banhammer::unbanExpired();
```

Or you can use the command:
```bash
php artisan banhammer:unban
```

To permanently delete all the expired bans :
```php
use Mchev\Banhammer\Banhammer;

Banhammer::clear();
```

Or you can use the command:
```bash
php artisan banhammer:clear
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Roadmap

- [ ] Handle UUIDs and ULIDs
- [ ] More tests
- [ ] Block IP range
- [ ] Auto block IP (Rate Limiting)
- [x] Cache
- [x] Ban history (expired, not expired)

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- Inspired by [laravel-ban](https://github.com/cybercog/laravel-ban) from [cybercog](https://github.com/cybercog)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
