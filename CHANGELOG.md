# Changelog

All notable changes to `bans-for-laravel` will be documented in this file.

## v1.2.0 - 2023-03-02

- Adding Metas (cutom properties) to bans.
- You may have to run `php artisan migrate` if you are upgrading from v1.1.x

## v1.1.5 - 2023-02-21

- Fix : Update cache on unban expired command

## v1.1.4 - 2023-02-21

- Adding created by relation in IPs collection.
- Removing ID in IPs collection.
- Grouping by IPs to prevent duplicate IPs with the banned() method on IP.
- Caching IP list for better performances

## v1.1.3 - 2023-02-13

- Fix nullable attribute expired_at

## v1.1.2 - 2023-02-13

- Fix missing alias middleware

## v1.1.1 - 2023-02-13

- New `logout.banned` middleware
- Removing auto logging out on `auth.banned`middleware

## v1.1.0 - 2023-02-12

- New IP Class
- Scopes expired(), notExpired()
- Documentation

## v1.0.0 - 2023-02-12

First release
