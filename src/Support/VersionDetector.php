<?php

namespace LaravelTools\Upgrade\Support;

use RuntimeException;

class VersionDetector
{
    public function full(): ?string
    {
        return $this->fromInstalledJson()
            ?? $this->fromComposerLock()
            ?? $this->fromComposerJson()
            ?? $this->fromArtisan();
    }

    public function major(): ?int
    {
        $full = $this->full();

        if ($full === null) {
            return null;
        }

        preg_match('/\d+/', $full, $matches);

        return isset($matches[0]) ? (int) $matches[0] : null;
    }

    public function isInstalled(string $package): bool
    {
        $installed = $this->installedPackages();

        if ($installed === null) {
            return false;
        }

        foreach ($installed as $entry) {
            if (($entry['name'] ?? null) === $package) {
                return true;
            }
        }

        return false;
    }

    private function fromInstalledJson(): ?string
    {
        $path = base_path('vendor/composer/installed.json');

        if (! is_file($path)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($path), true);

        if (! is_array($data)) {
            return null;
        }

        foreach (($data['packages'] ?? []) as $package) {
            if (($package['name'] ?? null) === 'laravel/framework') {
                return $package['version'] ?? null;
            }
        }

        return null;
    }

    private function fromComposerLock(): ?string
    {
        $path = base_path('composer.lock');

        if (! is_file($path)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($path), true);

        if (! is_array($data)) {
            return null;
        }

        $packages = array_merge($data['packages'] ?? [], $data['packages-dev'] ?? []);

        foreach ($packages as $package) {
            if (($package['name'] ?? null) === 'laravel/framework') {
                return $package['version'] ?? null;
            }
        }

        return null;
    }

    private function fromComposerJson(): ?string
    {
        $path = base_path('composer.json');

        if (! is_file($path)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($path), true);

        return $data['require']['laravel/framework']
            ?? $data['require-dev']['laravel/framework']
            ?? null;
    }

    private function fromArtisan(): ?string
    {
        $result = \Illuminate\Support\Facades\Process::path(base_path())
            ->run(['php', 'artisan', '--version']);

        if (! $result->successful()) {
            return null;
        }

        preg_match('/\d+(?:\.\d+){0,2}/', trim($result->output()), $matches);

        return $matches[0] ?? null;
    }

    private function installedPackages(): ?array
    {
        $path = base_path('vendor/composer/installed.json');

        if (! is_file($path)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($path), true);

        if (! is_array($data)) {
            return null;
        }

        return $data['packages'] ?? $data;
    }
}
