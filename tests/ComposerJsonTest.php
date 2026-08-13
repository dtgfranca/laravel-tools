<?php

namespace LaravelTools\Upgrade\Tests;

use LaravelTools\Upgrade\Support\ComposerJson;
use PHPUnit\Framework\TestCase;

class ComposerJsonTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = sys_get_temp_dir().'/composer_'.uniqid().'.json';

        file_put_contents($this->path, json_encode([
            'require' => [
                'php' => '^8.1',
                'laravel/framework' => '^10.0',
            ],
            'require-dev' => [
                'phpunit/phpunit' => '^10.0',
            ],
        ], JSON_PRETTY_PRINT));
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
    }

    public function test_read_and_write_preserves_data(): void
    {
        $composer = new ComposerJson($this->path);
        $composer->read();

        $this->assertSame('^10.0', $composer->get('require.laravel/framework'));

        $composer->setPhp('^8.2');
        $composer->requirePackage('laravel/sanctum', '^4.0');
        $composer->removePackage('phpunit/phpunit');
        $composer->write();

        $decoded = json_decode((string) file_get_contents($this->path), true);

        $this->assertSame('^8.2', $decoded['require']['php']);
        $this->assertSame('^4.0', $decoded['require']['laravel/sanctum']);
        $this->assertArrayNotHasKey('phpunit/phpunit', $decoded['require-dev']);
    }

    public function test_remove_package_from_both_require_and_require_dev(): void
    {
        $composer = new ComposerJson($this->path);
        $composer->read();
        $composer->removePackage('laravel/framework');
        $composer->write();

        $decoded = json_decode((string) file_get_contents($this->path), true);

        $this->assertArrayNotHasKey('laravel/framework', $decoded['require']);
    }

    public function test_update_package_only_when_present(): void
    {
        $composer = new ComposerJson($this->path);
        $composer->read();

        $this->assertTrue($composer->updatePackage('laravel/framework', '^13.0'));
        $this->assertTrue($composer->updatePackage('phpunit/phpunit', '^12.0', true));
        $this->assertFalse($composer->updatePackage('pestphp/pest', '^4.0'));
        $composer->write();

        $decoded = json_decode((string) file_get_contents($this->path), true);

        $this->assertSame('^13.0', $decoded['require']['laravel/framework']);
        $this->assertSame('^12.0', $decoded['require-dev']['phpunit/phpunit']);
        $this->assertArrayNotHasKey('pestphp/pest', $decoded['require']);
        $this->assertArrayNotHasKey('pestphp/pest', $decoded['require-dev']);
    }
}
