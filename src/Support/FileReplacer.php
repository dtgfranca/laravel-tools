<?php

namespace LaravelTools\Upgrade\Support;

use Illuminate\Filesystem\Filesystem;

class FileReplacer
{
    public function __construct(protected Filesystem $files) {}

    public function replaceInFiles(
        string $search,
        string $replace,
        array $paths = ['app', 'tests', 'bootstrap', 'config'],
        array $extensions = ['php'],
        bool $regex = false,
    ): array {
        $changed = [];

        foreach ($paths as $path) {
            $directory = base_path($path);

            if (! $this->files->isDirectory($directory)) {
                continue;
            }

            foreach ($this->files->allFiles($directory) as $file) {
                if (! in_array($file->getExtension(), $extensions, true)) {
                    continue;
                }

                $contents = $this->files->get($file->getPathname());

                $updated = $regex
                    ? preg_replace($search, $replace, $contents)
                    : str_replace($search, $replace, $contents);

                if ($updated === null || $updated === $contents) {
                    continue;
                }

                $this->files->put($file->getPathname(), $updated);

                $changed[] = str_replace(base_path(), '', $file->getPathname());
            }
        }

        return $changed;
    }

    public function setConfigValue(string $file, string $key, string $value): string
    {
        $path = base_path($file);

        if (! is_file($path)) {
            return "Arquivo {$file} não encontrado. Chave '{$key}' não adicionada.";
        }

        $contents = (string) file_get_contents($path);

        if (preg_match("/['\"]{$key}['\"]\s*=>/", $contents)) {
            return "Chave '{$key}' já existe em {$file}. Nada a fazer.";
        }

        $line = "    '{$key}' => {$value},";

        $updated = preg_replace(
            '/return\s*\[\s*/',
            "return [\n\n".$line."\n",
            $contents,
            1
        );

        if ($updated === null || $updated === $contents) {
            return "Não foi possível localizar o array de config em {$file}.";
        }

        file_put_contents($path, $updated);

        return "Chave '{$key}' adicionada em {$file}.";
    }
}
