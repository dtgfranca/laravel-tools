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
            Instructions::remove('adldap2/adldap2'),
            Instructions::remove('adldap2/adldap2-laravel'),
            Instructions::require('directorytree/ldaprecord', '^3.8.1'),
            Instructions::require('sentry/sentry-laravel', '^4.13.0'),
            Instructions::require('larastan/larastan', '^3.0'),
            Instructions::requireDev('phpstan/phpstan', '^2.0'),
            Instructions::require('nunomaduro/collision', '^8.1'),
            Instructions::requireDev('rector/rector', '^2.0'),
            Instructions::requireDev('phpunit/phpunit', '^11.0'),
            Instructions::require('tallstackui/tallstackui', '^2.5.2'),
            Instructions::requireDev('pestphp/pest', '^12.0'),
            Instructions::rectorSets([
                '\\RectorLaravel\\Set\\LaravelLevelSetList::UP_TO_LARAVEL_120',
            ]),
        ];
    }
}
