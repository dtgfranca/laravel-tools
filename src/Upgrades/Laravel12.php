<?php

namespace LaravelTools\Upgrade\Upgrades;

use LaravelTools\Upgrade\Contracts\Upgrade;
use LaravelTools\Upgrade\Support\Instructions;

class Laravel12 implements Upgrade
{
    public function from(): int
    {
        return 11;
    }

    public function to(): int
    {
        return 12;
    }

    public function instructions(): array
    {
        return [
            Instructions::setPhp('^8.3'),
            Instructions::require('laravel/framework', '^12.0'),
            Instructions::requireIfPresent('directorytree/ldaprecord', '^3.8.1', 'adldap2/adldap2'),
            Instructions::remove('adldap2/adldap2'),
            Instructions::remove('adldap2/adldap2-laravel'),
            Instructions::update('sentry/sentry-laravel', '^4.13.0'),
            Instructions::update('larastan/larastan', '^3.0'),
            Instructions::update('phpstan/phpstan', '^2.0', true),
            Instructions::update('nunomaduro/collision', '^8.1'),
            Instructions::update('rector/rector', '^2.0', true),
            Instructions::update('phpunit/phpunit', '^11.0', true),
            Instructions::update('tallstackui/tallstackui', '^2.5.2'),
            Instructions::update('pestphp/pest', '^12.0', true),
            Instructions::rectorSets([
                '\\RectorLaravel\\Set\\LaravelLevelSetList::UP_TO_LARAVEL_120',
            ]),
        ];
    }
}
