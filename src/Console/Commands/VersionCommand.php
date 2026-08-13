<?php

namespace LaravelTools\Upgrade\Console\Commands;

use Illuminate\Console\Command;
use LaravelTools\Upgrade\Support\VersionDetector;

class VersionCommand extends Command
{
    protected $signature = 'laravel:version';

    protected $description = 'Detecta a versão atual do Laravel.';

    public function handle(VersionDetector $detector): int
    {
        $this->info('Versão detectada: '.($detector->full() ?? 'desconhecida'));
        $this->info('Major: '.($detector->major() ?? 'desconhecida'));

        return self::SUCCESS;
    }
}
