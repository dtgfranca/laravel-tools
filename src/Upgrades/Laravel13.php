<?php

namespace LaravelTools\Upgrade\Upgrades;

use LaravelTools\Upgrade\Contracts\Upgrade;
use LaravelTools\Upgrade\Support\Instructions;

class Laravel13 implements Upgrade
{
    public function from(): int
    {
        return 12;
    }

    public function to(): int
    {
        return 13;
    }

    public function instructions(): array
    {
        return [
            Instructions::require('laravel/framework', '^13.0'),
            Instructions::update('laravel/tinker', '^3.0'),
            Instructions::update('laravel/boost', '^2.0'),
            Instructions::update('phpunit/phpunit', '^12.0', true),
            Instructions::update('pestphp/pest', '^4.0', true),
            Instructions::replaceInFiles(
                'VerifyCsrfToken',
                'PreventRequestForgery',
                ['app', 'tests', 'bootstrap', 'config'],
                ['php']
            ),
            Instructions::setConfigValue('config/cache.php', 'serializable_classes', 'false'),
            Instructions::replaceInFile(
                'config/session.php',
                "'serialization' => 'php'",
                "'serialization' => 'json'"
            ),
            Instructions::rectorSets([
                '\\RectorLaravel\\Set\\LaravelLevelSetList::UP_TO_LARAVEL_130',
            ]),
        ];
    }
}
