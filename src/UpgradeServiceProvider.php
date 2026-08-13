<?php

namespace LaravelTools\Upgrade;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use LaravelTools\Upgrade\Console\Commands\UpgradeCommand;
use LaravelTools\Upgrade\Console\Commands\VersionCommand;
use LaravelTools\Upgrade\Support\BackupManager;
use LaravelTools\Upgrade\Support\ComposerJson;
use LaravelTools\Upgrade\Support\FileReplacer;
use LaravelTools\Upgrade\Support\MigrationPublisher;
use LaravelTools\Upgrade\Support\RectorRunner;
use LaravelTools\Upgrade\Support\VersionDetector;
use LaravelTools\Upgrade\Upgrades\Laravel10;
use LaravelTools\Upgrade\Upgrades\Laravel11;
use LaravelTools\Upgrade\Upgrades\Laravel12;
use LaravelTools\Upgrade\Upgrades\Laravel13;
use LaravelTools\Upgrade\Upgrades\Laravel9;

class UpgradeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-upgrade.php', 'laravel-upgrade');

        $this->app->singleton(VersionDetector::class);

        $this->app->singleton(UpgradeManager::class, function ($app) {
            return new UpgradeManager(
                $app->make(VersionDetector::class),
                new ComposerJson(base_path('composer.json')),
                new BackupManager($app->make(Filesystem::class)),
                $app->make(RectorRunner::class),
                $app->make(MigrationPublisher::class),
                $app->make(FileReplacer::class),
                [
                    new Laravel9(),
                    new Laravel10(),
                    new Laravel11(),
                    new Laravel12(),
                    new Laravel13(),
                ],
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/laravel-upgrade.php' => config_path('laravel-upgrade.php'),
            ], 'laravel-upgrade-config');

            $this->commands([
                UpgradeCommand::class,
                VersionCommand::class,
            ]);
        }
    }
}
