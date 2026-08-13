<?php

namespace LaravelTools\Upgrade\Tests;

use Illuminate\Filesystem\Filesystem;
use LaravelTools\Upgrade\Support\FileReplacer;
use Orchestra\Testbench\TestCase;

class FileReplacerTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = 'upgrade_test_'.uniqid();

        $this->files()->ensureDirectoryExists(base_path($this->dir));
    }

    protected function tearDown(): void
    {
        $this->files()->deleteDirectory(base_path($this->dir));

        parent::tearDown();
    }

    public function test_replace_in_files_only_touches_matching_extensions(): void
    {
        $this->files()->put(base_path($this->dir.'/a.php'), "<?php\nuse App\\Foo\\VerifyCsrfToken;\n");
        $this->files()->put(base_path($this->dir.'/b.txt'), 'VerifyCsrfToken');

        $replacer = new FileReplacer($this->files());

        $changed = $replacer->replaceInFiles('VerifyCsrfToken', 'PreventRequestForgery', [$this->dir], ['php']);

        $this->assertSame(['/'.$this->dir.'/a.php'], $changed);
        $this->assertStringContainsString(
            'PreventRequestForgery',
            $this->files()->get(base_path($this->dir.'/a.php'))
        );
        $this->assertStringContainsString(
            'VerifyCsrfToken',
            $this->files()->get(base_path($this->dir.'/b.txt'))
        );
    }

    public function test_set_config_value_inserts_key(): void
    {
        $config = "<?php\n\nreturn [\n    'default' => env('CACHE_STORE', 'database'),\n];\n";

        $this->files()->put(base_path($this->dir.'/cache.php'), $config);

        $replacer = new FileReplacer($this->files());

        $replacer->setConfigValue($this->dir.'/cache.php', 'serializable_classes', 'false');

        $this->assertStringContainsString(
            "'serializable_classes' => false",
            $this->files()->get(base_path($this->dir.'/cache.php'))
        );
    }

    public function test_set_config_value_is_idempotent(): void
    {
        $config = "<?php\n\nreturn [\n    'serializable_classes' => false,\n];\n";

        $this->files()->put(base_path($this->dir.'/cache.php'), $config);

        $replacer = new FileReplacer($this->files());

        $message = $replacer->setConfigValue($this->dir.'/cache.php', 'serializable_classes', 'false');

        $this->assertStringContainsString('já existe', $message);
    }

    private function files(): Filesystem
    {
        return $this->app->make(Filesystem::class);
    }
}
