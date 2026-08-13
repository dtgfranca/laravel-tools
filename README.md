# Laravel Tools

Automated Laravel version upgrade toolkit. This package detects your current
Laravel version and upgrades your application to the next major version:
dependency bumps, file/class renames, config changes, migration publishing and
(optionally) code transformations via [Rector](https://github.com/rectorphp/rector).

## Requirements

- PHP 8.2+
- Laravel 11 or 12 (the package itself; the target project can be older)

> For automated code transformation (AST), install `rector/rector` and
> `rector/rector-laravel` in the target project. The upgrade works without it,
> skipping only the Rector step.

## Installation

```bash
composer require dtgfranca/laravel-tools
```

The service provider is auto-discovered via `extra.laravel.providers`.

## Usage

### Check the current Laravel version

```bash
php artisan laravel:version
```

### Upgrade interactively

```bash
php artisan laravel:upgrade
```

You will be prompted to choose the target version.

### Upgrade directly to a version

```bash
php artisan laravel:upgrade 12
```

### Available options

```bash
php artisan laravel:upgrade --dry-run        # simulate, change nothing
php artisan laravel:upgrade 12 --skip-tests  # do not run the test suite
php artisan laravel:upgrade 12 --skip-backup # do not create a backup
```

## Supported upgrade paths

| From | To |
|------|----|
| Laravel 8  | Laravel 9  |
| Laravel 9  | Laravel 10 |
| Laravel 10 | Laravel 11 |
| Laravel 11 | Laravel 12 |
| Laravel 12 | Laravel 13 |

## What each upgrade does

1. Detects the current version from `vendor/composer/installed.json`.
2. Creates a backup under `backup_pre_update_<timestamp>/`.
3. Updates `composer.json` (framework, packages, PHP constraint).
4. Runs `composer update --with-all-dependencies`.
5. Applies file/class renames (e.g. `VerifyCsrfToken` → `PreventRequestForgery`).
6. Runs Rector sets (if installed).
7. Publishes package migrations (Sanctum, Passport, Cashier, etc.).
8. Clears caches and runs the test suite.

## Configuration

Publish the config file to customize backup paths, composer command, Rector
binary and test command:

```bash
php artisan vendor:publish --tag=laravel-upgrade-config
```

## Legacy shell scripts

The original bash scripts are kept in the `legacy/` directory for reference.
