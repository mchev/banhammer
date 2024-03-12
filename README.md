# Banhammer, a Model, IP and Country ban package for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mchev/banhammer.svg?style=flat-square)](https://packagist.org/packages/mchev/banhammer)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mchev/banhammer/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mchev/banhammer/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mchev/banhammer.svg?style=flat-square)](https://packagist.org/packages/mchev/banhammer)
[![Package for laravel](https://img.shields.io/badge/Package%20for%20Laravel-grey.svg?style=flat-square&logo=laravel&logoColor=white)](https://packagist.org/packages/mchev/banhammer)

Banhammer for Laravel offers a very simple way to ban any Model by ID and by IP. It also allows to block requests by IP addresses.

Banned models can have an expiration date and will be automatically unbanned using the Scheduler.

## Table of Contents
1. [Introduction](#banhammer-a-model-and-ip-ban-package-for-laravel)
2. [Version Compatibility](#version-compatibility)
3. [Installation](#installation)
4. [Upgrading To 2.0 from 1.x](#upgrading-to-20-from-1x)
5. [Usage](#usage)
   - [Making a Model Bannable](#to-make-a-model-bannable-add-the-mchevbanhammertraitsbannable-trait-to-the-model)
   - [Ban / Unban](#ban--unban)
   - [IP](#ip)
   - [Metas](#metas)
   - [Blocking Access from Specific Countries](#blocking-access-from-specific-countries)
   - [Middleware](#middleware)
   - [Scheduler](#scheduler)
   - [Events](#events)
   - [Miscellaneous](#misc)
6. [Testing](#testing)
7. [Changelog](#changelog)
8. [Roadmap / Todo](#roadmap--todo)
9. [Contributing](#contributing)
10. [Security Vulnerabilities](#security-vulnerabilities)
11. [Credits](#credits)
12. [License](#license)

## Version Compatibility

 Laravel        | Banhammer
:---------------------|:----------
 ^9.0                 | 1.x, 2.x
 ^10.0                | 1.x, 2.x
 ^11.0                | 1.x, 2.x

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

## Upgrading To 2.0 from 1.x

To upgrade to Banhammer version 2.0, follow these simple steps:

1. Update the package version in your application's `composer.json` file:

```json
"require": {
    "mchev/banhammer": "^2.0"
}
```

2. Run the following command in your terminal:

```bash
composer update mchev/banhammer
```

3. Update the configuration

    1. Update the configuration
        - Backup your previous configuration file located at `config/ban.php`.
        - Force republish the new configuration using the command: 
        ```bash
        php artisan vendor:publish --tag="banhammer-config" --force
        ```
        - Review the new configuration file and make any necessary edits.

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

// Ban IP with expiration date
IP::ban("8.8.8.8", [], now()->addMinutes(10));

// Full
IP::ban(
    "8.8.8.8", 
    [
        "MetaKey1" => "MetaValue1",
        "MetaKey2" => "MetaValue2",
    ], 
    now()->addMinutes(10)
);
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

### Blocking Access from Specific Countries

To enhance the security of your application, you can restrict access from specific countries by enabling the country-blocking feature in the configuration file. Follow these simple steps:

1. Open your Banhammer configuration file (config/ban.php).

2. Set the 'block_by_country' configuration option to true to enable country-based blocking.

```php
'block_by_country' => true,
```

3. Specify the list of countries you want to block by adding their country codes to the 'blocked_countries' array.

```php
'blocked_countries' => ['FR', 'ES'],
```

By configuring these settings, you effectively block access to your application for users originating from the specified countries. This helps improve the security and integrity of your system by preventing unwanted traffic from regions you've identified as potential risks.

**Important Notice:**
The Banhammer package utilizes the free version of ip-api.com for geolocation data. Keep in mind that their endpoints have a rate limit of 45 HTTP requests per minute from a single IP address. If you exceed this limit, your requests will be throttled, and you may receive a 429 HTTP status code until your rate limit window is reset.

> **Developer Note:**
> While Banhammer currently relies on the free version of [ip-api.com](https://ip-api.com/) for geolocation data, I'm open to exploring better alternatives. If you have suggestions for a more robust or efficient solution, or if you'd like to contribute improvements, please feel free to [open an issue](https://github.com/mchev/banhammer/issues) or submit a [pull request](https://github.com/mchev/banhammer/pulls).

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

## Roadmap / Todo

- [ ] Laravel pulse card (ips banned, block by country enabled, etc.).
- [x] Block by country feature

## Contributing

To encourage active collaboration, Banhammer strongly encourages pull requests, not just bug reports. Pull requests will only be reviewed when marked as "ready for review" (not in the "draft" state) and all tests for new features are passing. Lingering, non-active pull requests left in the "draft" state will be closed after a few days.

However, if you file a bug report, your issue should contain a title and a clear description of the issue. You should also include as much relevant information as possible and a code sample that demonstrates the issue. The goal of a bug report is to make it easy for yourself - and others - to replicate the bug and develop a fix.

Remember, bug reports are created in the hope that others with the same problem will be able to collaborate with you on solving it. Do not expect that the bug report will automatically see any activity or that others will jump to fix it. 

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- Inspired by [laravel-ban](https://github.com/cybercog/laravel-ban) from [cybercog](https://github.com/cybercog)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
