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
            Instructions::require('laravel/sanctum', '^3.2'),
            Instructions::require('laravel/passport', '^11.0'),
            Instructions::require('laravel/ui', '^4.0'),
            Instructions::require('doctrine/dbal', '^3.0'),
            Instructions::require('spatie/laravel-ignition', '^2.0'),
            Instructions::require('ylsideas/feature-flags', '^3.0'),
            Instructions::require('nunomaduro/collision', '^7.0'),
            Instructions::requireDev('phpunit/phpunit', '^10.0'),
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
