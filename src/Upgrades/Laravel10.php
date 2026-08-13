<?php

namespace LaravelTools\Upgrade\Upgrades;

use LaravelTools\Upgrade\Contracts\Upgrade;
use LaravelTools\Upgrade\Support\Instructions;

class Laravel10 implements Upgrade
{
    public function from(): int
    {
        return 9;
    }

    public function to(): int
    {
        return 10;
    }

    public function instructions(): array
    {
        return [
            Instructions::setPhp('^8.1'),
            Instructions::remove('fruitcake/laravel-cors'),
            Instructions::replaceInFile(
                'app/Http/Kernel.php',
                '\\Fruitcake\\Cors\\HandleCors::class',
                '\\Illuminate\\Http\\Middleware\\HandleCors::class'
            ),
            Instructions::require('laravel/framework', '^10.0'),
            Instructions::update('laravel/sanctum', '^3.2'),
            Instructions::update('laravel/passport', '^11.0'),
            Instructions::update('laravel/ui', '^4.0'),
            Instructions::update('doctrine/dbal', '^3.0'),
            Instructions::update('spatie/laravel-ignition', '^2.0'),
            Instructions::update('ylsideas/feature-flags', '^3.0'),
            Instructions::update('nunomaduro/collision', '^7.0'),
            Instructions::update('phpunit/phpunit', '^10.0', true),
            Instructions::replaceInFile(
                'phpunit.xml',
                '/\s*processUncoveredFiles="[^"]*"/',
                '',
                true
            ),
            Instructions::rectorSets([
                '\\RectorLaravel\\Set\\LaravelLevelSetList::UP_TO_LARAVEL_100',
            ]),
        ];
    }
}
