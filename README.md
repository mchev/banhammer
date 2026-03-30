# Banhammer 🔨

> A simple and powerful ban package for Laravel - ban models, IPs, and countries with ease.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mchev/banhammer.svg?style=flat-square)](https://packagist.org/packages/mchev/banhammer)
[![GitHub Tests Action Status](https://github.com/mchev/banhammer/actions/workflows/run-tests.yml/badge.svg)](https://github.com/mchev/banhammer/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/mchev/banhammer.svg?style=flat-square)](https://packagist.org/packages/mchev/banhammer)
[![Package for laravel](https://img.shields.io/badge/Package%20for%20Laravel-grey.svg?style=flat-square&logo=laravel&logoColor=white)](https://packagist.org/packages/mchev/banhammer)

Banhammer allows you to ban any Eloquent model, IP addresses, and even entire countries. Bans can be permanent or temporary with automatic expiration.

---

## 📋 Table of Contents

- [Quick Start](#-quick-start)
- [Features](#-features)
- [Installation](#-installation)
- [Usage Guide](#-usage-guide)
  - [Banning Models](#banning-models)
  - [Banning IPs](#banning-ips)
  - [Country Blocking](#country-blocking)
  - [Middleware](#middleware)
  - [Scheduler](#scheduler)
  - [Events](#events)
- [Advanced Topics](#-advanced-topics)
  - [Metas](#metas)
  - [UUIDs](#uuids)
  - [Upgrading](#upgrading-to-20-from-1x)
- [Development](#-development)

---

## 🚀 Quick Start

```bash
composer require mchev/banhammer
php artisan vendor:publish --provider="Mchev\Banhammer\BanhammerServiceProvider" --tag="migrations"
php artisan migrate
```

Add the trait to your model:

```php
use Mchev\Banhammer\Traits\Bannable;

class User extends Model
{
    use Bannable;
}
```

Ban a user:

```php
$user->ban();
```

---

## ✨ Features

- ✅ Ban any Eloquent model (User, Team, etc.)
- ✅ Ban IP addresses (single or multiple)
- ✅ Block entire countries
- ✅ Temporary or permanent bans
- ✅ Automatic expiration handling
- ✅ Middleware protection
- ✅ Event system
- ✅ Metadata support

---

## 📦 Installation

### Requirements

- PHP 8.0+

### Laravel compatibility

| Laravel | Supported since Banhammer |
|--------|----------------------------|
| 9.x    | v1.0.0 |
| 10.x   | v1.0.0 |
| 11.x   | v2.2.0 |
| 12.x   | v2.4.0 |
| 13.x   | v2.5.0 |

### Setup

1. **Install the package:**
   ```bash
   composer require mchev/banhammer
   ```

2. **Publish and run migrations:**
   ```bash
   php artisan vendor:publish --provider="Mchev\Banhammer\BanhammerServiceProvider" --tag="migrations"
   php artisan migrate
   ```

3. **Publish config (optional):**
   ```bash
   php artisan vendor:publish --provider="Mchev\Banhammer\BanhammerServiceProvider" --tag="config"
   ```

   > 💡 The config file allows you to customize table name, model, fallback URLs, and more.

---

## 📖 Usage Guide

### Banning Models

#### Make a Model Bannable

Add the `Bannable` trait to any model:

```php
use Mchev\Banhammer\Traits\Bannable;

class User extends Model
{
    use Bannable;
}
```

> 💡 You can add the trait to multiple models (User, Team, Group, etc.)

#### Basic Operations

| Action | Code |
|--------|------|
| **Ban a user** | `$user->ban()` |
| **Ban with expiration** | `$user->banUntil('2 days')` |
| **Check if banned** | `$user->isBanned()` |
| **Check if not banned** | `$user->isNotBanned()` |
| **Unban** | `$user->unban()` |

#### Advanced Ban Options

```php
$user->ban([
    'comment' => "You've been evil",
    'ip' => "8.8.8.8",
    'expired_at' => Carbon::now()->addDays(7),
    'created_by_type' => 'App\Models\Admin',
    'created_by_id' => auth()->id(),
    'metas' => [
        'route' => request()->route()->getName(),
        'user_agent' => request()->header('user-agent')
    ]
]);
```

> ⚠️ Without `expired_at`, the ban is permanent.

#### Query Scopes

```php
// Get banned users
$bannedUsers = User::banned()->get();

// Get non-banned users
$activeUsers = User::notBanned()->get();

// Alternative syntax
$activeUsers = User::banned(false)->get();
```

#### List Bans

```php
// All bans for a model
$bans = $user->bans()->get();

// Only expired bans
$expired = $user->bans()->expired()->get();

// Active bans
$active = $user->bans()->notExpired()->get();
```

---

### Banning IPs

#### Basic Operations

```php
use Mchev\Banhammer\IP;

// Ban single IP
IP::ban("8.8.8.8");

// Ban multiple IPs
IP::ban(["8.8.8.8", "4.4.4.4", "1.1.1.1"]);

// Ban with expiration
IP::ban("8.8.8.8", [], now()->addMinutes(10));

// Ban with metadata
IP::ban("8.8.8.8", [
    'reason' => 'spam',
    'severity' => 'high'
]);
```

#### Unban IPs

```php
// Unban single IP
IP::unban("8.8.8.8");

// Unban multiple IPs
IP::unban(["8.8.8.8", "4.4.4.4"]);
```

#### Check & List Banned IPs

```php
// Check if IP is banned
IP::isBanned("8.8.8.8"); // bool

// Get all banned IPs
$ips = IP::banned()->get(); // Collection
$ips = IP::banned()->pluck('ip')->toArray(); // Array
```

---

### Country Blocking

Block access from specific countries automatically.

#### Configuration

1. **Enable country blocking** in `config/ban.php`:
   ```php
   'block_by_country' => true,
   ```

2. **Specify blocked countries**:
   ```php
   'blocked_countries' => ['FR', 'ES', 'US'],
   ```

That's it! The middleware will automatically block requests from these countries.

> ⚠️ **Rate Limit Notice:** The free version of ip-api.com has a limit of 45 requests/minute. Exceeding this will result in 429 errors until the limit resets.

> 💡 **Want to improve this?** If you have suggestions for better geolocation services or want to contribute improvements, please [open an issue](https://github.com/mchev/banhammer/issues) or submit a [pull request](https://github.com/mchev/banhammer/pulls).

---

### Middleware

Protect your routes with ban middleware:

#### Available Middleware

| Middleware | Description |
|-----------|-------------|
| `auth.banned` | Blocks banned users |
| `ip.banned` | Blocks banned IPs |
| `logout.banned` | Logs out and blocks banned users/IPs |

#### Usage

```php
// Single route
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth.banned');

// Route group
Route::middleware(['auth.banned'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::get('/settings', [SettingsController::class, 'index']);
});

// Block IPs on all routes
// Add to app/Http/Kernel.php:
protected $middleware = [
    // ...
    \Mchev\Banhammer\Middleware\IPBanned::class,
];
```

> 💡 **Tip:** `logout.banned` includes the functionality of both `auth.banned` and `ip.banned`, so you don't need to use them together.

---

### Scheduler

Banhammer automatically deletes expired bans using Laravel's scheduler.

#### Setup

> ⚠️ **Important:** You must have a cron job running Laravel's scheduler:
> ```bash
> * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
> ```
> 
> See: [Laravel Scheduler Docs](https://laravel.com/docs/scheduling#running-the-scheduler)

#### Configuration

By default, the `banhammer:unban` command runs **every minute**. You can customize this:

**Disable automatic scheduler:**
```php
// config/ban.php
'scheduler_enabled' => false,
```

Or via environment:
```env
BANHAMMER_SCHEDULER_ENABLED=false
```

**Change frequency:**
```php
// config/ban.php
'scheduler_periodicity' => 'everyFiveMinutes', // or 'hourly', 'daily', etc.
```

Or via environment:
```env
BANHAMMER_SCHEDULER_PERIODICITY=everyFiveMinutes
```

**Available frequencies:** `everyMinute`, `everyFiveMinutes`, `everyTenMinutes`, `everyFifteenMinutes`, `everyThirtyMinutes`, `hourly`, `daily`, `twiceDaily`, etc.

---

### Events

Listen to ban/unban events:

```php
use Mchev\Banhammer\Events\ModelWasBanned;
use Mchev\Banhammer\Events\ModelWasUnbanned;

Event::listen(ModelWasBanned::class, function ($event) {
    // User was banned
    Log::info("User {$event->ban->bannable->id} was banned");
});

Event::listen(ModelWasUnbanned::class, function ($event) {
    // User was unbanned
    Log::info("User {$event->ban->bannable->id} was unbanned");
});
```

---

## 🔧 Advanced Topics

### Metas

Store additional data with bans:

```php
// Set meta
$ban->setMeta('username', 'Jane');
$ban->setMeta('reason', 'spam');

// Get meta
$ban->getMeta('username'); // 'Jane'

// Check if meta exists
$ban->hasMeta('username'); // true

// Remove meta
$ban->forgetMeta('username');
```

#### Filter by Meta

```php
// Find bans with specific meta
IP::banned()->whereMeta('username', 'Jane')->get();
$user->bans()->whereMeta('reason', 'spam')->get();
User::whereBansMeta('username', 'Jane')->get();
```

#### Ban with Metas

```php
// When banning
$user->ban([
    'metas' => [
        'route' => request()->route()->getName(),
        'user_agent' => request()->header('user-agent')
    ]
]);

IP::ban("8.8.8.8", [
    'reason' => 'spam',
    'severity' => 'high'
]);
```

---

### UUIDs

To use UUIDs instead of auto-incrementing IDs:

1. **Publish migrations:**
   ```bash
   php artisan vendor:publish --provider="Mchev\Banhammer\BanhammerServiceProvider" --tag="migrations"
   ```

2. **Edit the migration:**
   ```diff
   - $table->id();
   + $table->uuid('id');
   ```

3. **Create a custom Ban model:**
   ```php
   namespace App\Models;
   
   use Illuminate\Database\Eloquent\Concerns\HasUuids;
   use Mchev\Banhammer\Models\Ban as BanhammerBan;
   
   class Ban extends BanhammerBan
   {
       use HasUuids;
   }
   ```

4. **Update config:**
   ```php
   // config/ban.php
   'model' => \App\Models\Ban::class,
   ```

---

### Upgrading To 2.0 from 1.x

1. **Update composer.json:**
   ```json
   "require": {
       "mchev/banhammer": "^2.0"
   }
   ```

2. **Update the package:**
   ```bash
   composer update mchev/banhammer
   ```

3. **Update configuration:**
   ```bash
   # Backup your current config
   cp config/ban.php config/ban.php.backup
   
   # Republish config
   php artisan vendor:publish --provider="Mchev\Banhammer\BanhammerServiceProvider" --tag="config" --force
   
   # Review and merge any custom settings
   ```

---

## 🛠️ Development

### Commands

```bash
# Manually delete expired bans
php artisan banhammer:unban

# Permanently delete all expired bans
php artisan banhammer:clear
```

### Programmatic Usage

```php
use Mchev\Banhammer\Banhammer;

// Delete expired bans
Banhammer::unbanExpired();

// Permanently delete expired bans
Banhammer::clear();
```

### Testing

```bash
composer test
```

---

## 🤝 Contributing

We welcome contributions! Please:

- Open issues for bug reports or feature requests
- Submit pull requests (mark as "ready for review")
- Ensure all tests pass
- Follow Laravel coding standards

> 💡 Pull requests in "draft" state will be closed after a few days of inactivity.

---

## 🙏 Credits

Inspired by [laravel-ban](https://github.com/cybercog/laravel-ban) from [cybercog](https://github.com/cybercog).

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
