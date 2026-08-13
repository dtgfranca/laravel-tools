<?php

return [
    'backup' => [
        'enabled' => true,
        'directory' => env('LARAVEL_UPGRADE_BACKUP_DIR', 'backup_pre_update'),
        'paths' => [
            'composer.json',
            'composer.lock',
            'app',
            'config',
            'routes',
            'database',
            'bootstrap',
        ],
    ],

    'composer' => [
        'update_command' => ['composer', 'update', '--with-all-dependencies', '--no-interaction'],
    ],

    'rector' => [
        'binary' => env('LARAVEL_UPGRADE_RECTOR_BINARY', 'vendor/bin/rector'),
        'config' => env('LARAVEL_UPGRADE_RECTOR_CONFIG', 'rector-upgrade.php'),
        'paths' => [
            'app',
            'config',
            'routes',
            'database',
            'bootstrap',
        ],
    ],

    'tests' => [
        'enabled' => true,
        'command' => ['php', 'artisan', 'test'],
    ],
];
