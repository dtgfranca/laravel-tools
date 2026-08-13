<?php

namespace LaravelTools\Upgrade\Support;

use Illuminate\Filesystem\Filesystem;

class BackupManager
{
    public function __construct(protected Filesystem $files) {}

    public function create(array $paths, string $baseDir): string
    {
        $target = base_path($baseDir.'_'.date('Ymd_His'));

        while ($this->files->exists($target)) {
            $target = base_path($baseDir.'_'.date('Ymd_His').'_'.bin2hex(random_bytes(2)));
        }

        $this->files->makeDirectory($target, 0755, true);

        foreach ($paths as $path) {
            $source = base_path($path);

            if (! $this->files->exists($source)) {
                continue;
            }

            $destination = $target.DIRECTORY_SEPARATOR.$path;

            if ($this->files->isDirectory($source)) {
                $this->files->copyDirectory($source, $destination);
            } else {
                $this->files->ensureDirectoryExists(dirname($destination));
                $this->files->copy($source, $destination);
            }
        }

        return $target;
    }
}
