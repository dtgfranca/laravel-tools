<?php

namespace LaravelTools\Upgrade\Upgrades;

use LaravelTools\Upgrade\Contracts\Upgrade;
use LaravelTools\Upgrade\Support\Instructions;

class Laravel9 implements Upgrade
{
    public function from(): int
    {
        return 8;
    }

    public function to(): int
    {
        return 9;
    }

    public function instructions(): array
    {
        return [
            Instructions::setPhp('^8.0.2'),
            Instructions::require('laravel/framework', '^9.0'),
            Instructions::require('nunomaduro/collision', '^6.1'),
            Instructions::remove('facade/ignition'),
            Instructions::require('spatie/laravel-ignition', '^1.0'),
            Instructions::require('pusher/pusher-php-server', '^5.0'),
            Instructions::remove('fideloper/proxy'),
            Instructions::replaceInFile(
                'app/Http/Middleware/TrustProxies.php',
                'protected $headers = Request::HEADER_X_FORWARDED_ALL;',
                "protected \$headers =\n        Request::HEADER_X_FORWARDED_FOR |\n        Request::HEADER_X_FORWARDED_HOST |\n        Request::HEADER_X_FORWARDED_PORT |\n        Request::HEADER_X_FORWARDED_PROTO |\n        Request::HEADER_X_FORWARDED_AWS_ELB;"
            ),
            Instructions::rectorSets([
                '\\RectorLaravel\\Set\\LaravelLevelSetList::UP_TO_LARAVEL_90',
            ]),
        ];
    }
}
