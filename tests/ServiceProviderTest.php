<?php

namespace LaravelTools\Upgrade\Tests;

use LaravelTools\Upgrade\UpgradeManager;
use LaravelTools\Upgrade\UpgradeServiceProvider;
use Orchestra\Testbench\TestCase;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [UpgradeServiceProvider::class];
    }

    public function test_upgrade_manager_resolves_with_all_upgrades(): void
    {
        $manager = $this->app->make(UpgradeManager::class);

        $this->assertCount(5, $manager->upgrades());
        $this->assertSame(13, $manager->upgrades()[4]->to());
    }

    public function test_version_command_is_registered(): void
    {
        $this->artisan('laravel:version')->assertExitCode(0);
    }
}
