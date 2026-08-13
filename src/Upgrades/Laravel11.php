<?php

namespace LaravelTools\Upgrade\Upgrades;

use LaravelTools\Upgrade\Contracts\Upgrade;
use LaravelTools\Upgrade\Support\Instructions;

class Laravel11 implements Upgrade
{
    public function from(): int
    {
        return 10;
    }

    public function to(): int
    {
        return 11;
    }

    public function instructions(): array
    {
        return [
            Instructions::setPhp('^8.2'),
            Instructions::require('laravel/framework', '^11.0'),
            Instructions::requireIfPresent('directorytree/ldaprecord', '^3.8.1', 'adldap2/adldap2'),
            Instructions::remove('adldap2/adldap2'),
            Instructions::remove('adldap2/adldap2-laravel'),
            Instructions::update('sentry/sentry-laravel', '^4.13.0'),
            Instructions::update('spatie/laravel-permission', '^6.17'),
            Instructions::update('nunomaduro/collision', '^8.1'),
            Instructions::update('laravel/sanctum', '^4.0'),
            Instructions::update('laravel/breeze', '^2.0'),
            Instructions::update('laravel/cashier', '^15.0'),
            Instructions::update('laravel/dusk', '^8.0'),
            Instructions::update('laravel/jetstream', '^5.0'),
            Instructions::update('laravel/octane', '^2.3'),
            Instructions::update('laravel/passport', '^12.0'),
            Instructions::update('laravel/scout', '^10.0'),
            Instructions::update('laravel/spark-stripe', '^5.0'),
            Instructions::update('laravel/telescope', '^5.0'),
            Instructions::update('livewire/livewire', '^3.4'),
            Instructions::update('inertiajs/inertia-laravel', '^1.0'),
            Instructions::publishMigrations('laravel/sanctum', 'sanctum-migrations'),
            Instructions::publishMigrations('laravel/telescope', 'telescope-migrations'),
            Instructions::publishMigrations('laravel/passport', 'passport-migrations'),
            Instructions::publishMigrations('laravel/cashier', 'cashier-migrations'),
            Instructions::publishMigrations('laravel/spark-stripe', 'spark-migrations'),
            Instructions::rectorSets([
                '\\RectorLaravel\\Set\\LaravelLevelSetList::UP_TO_LARAVEL_110',
            ]),
        ];
    }
}
