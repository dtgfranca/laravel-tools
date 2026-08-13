<?php

namespace LaravelTools\Upgrade\Support;

use RuntimeException;

class ComposerJson
{
    protected array $data = [];

    public function __construct(protected string $path) {}

    public function path(): string
    {
        return $this->path;
    }

    public function read(): array
    {
        if (! is_file($this->path)) {
            throw new RuntimeException("composer.json não encontrado em {$this->path}.");
        }

        $decoded = json_decode((string) file_get_contents($this->path), true);

        if (! is_array($decoded)) {
            throw new RuntimeException("composer.json inválido em {$this->path}.");
        }

        return $this->data = $decoded;
    }

    public function write(): void
    {
        $json = json_encode(
            $this->data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        file_put_contents($this->path, $json."\n");
    }

    public function setPhp(string $constraint): void
    {
        $this->data['require']['php'] = $constraint;
    }

    public function requirePackage(string $package, string $constraint): void
    {
        $this->data['require'][$package] = $constraint;
    }

    public function requireDevPackage(string $package, string $constraint): void
    {
        $this->data['require-dev'][$package] = $constraint;
    }

    public function removePackage(string $package): void
    {
        unset($this->data['require'][$package], $this->data['require-dev'][$package]);
    }

    public function hasPackage(string $package): bool
    {
        return isset($this->data['require'][$package])
            || isset($this->data['require-dev'][$package]);
    }

    public function updatePackage(string $package, string $constraint, bool $dev = false): bool
    {
        $section = $dev ? 'require-dev' : 'require';

        if (isset($this->data[$section][$package])) {
            $this->data[$section][$package] = $constraint;

            return true;
        }

        if (! $dev && isset($this->data['require-dev'][$package])) {
            $this->data['require-dev'][$package] = $constraint;

            return true;
        }

        return false;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);

        $value = $this->data;

        foreach ($keys as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
