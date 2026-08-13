<?php

namespace LaravelTools\Upgrade\Support;

use Illuminate\Support\Facades\Process;

class MigrationPublisher
{
    public function __construct(protected VersionDetector $detector) {}

    public function publish(string $package, string $tag, ?\Closure $output = null): array
    {
        if (! $this->detector->isInstalled($package)) {
            return ["Pacote {$package} não está instalado. Nada a publicar."];
        }

        $result = Process::path(base_path())
            ->timeout(0)
            ->run(['php', 'artisan', 'vendor:publish', '--tag='.$tag, '--force'], $output);

        return [
            $result->successful()
                ? "Migrations do pacote {$package} publicadas ({$tag})."
                : "Falha ao publicar migrations de {$package}: ".trim($result->errorOutput()),
        ];
    }
}
