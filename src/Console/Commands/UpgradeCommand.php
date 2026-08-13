<?php

namespace LaravelTools\Upgrade\Console\Commands;

use Illuminate\Console\Command;
use LaravelTools\Upgrade\UpgradeManager;
use RuntimeException;

class UpgradeCommand extends Command
{
    protected $signature = 'laravel:upgrade
                            {to? : Versão alvo (ex.: 12)}
                            {--dry-run : Simula a atualização sem aplicar mudanças}
                            {--skip-tests : Não roda a suíte de testes}
                            {--skip-backup : Não cria backup}';

    protected $description = 'Atualiza a aplicação Laravel para a versão alvo.';

    public function handle(UpgradeManager $manager): int
    {
        $to = (int) ($this->argument('to') ?? $this->choice(
            'Para qual versão deseja atualizar?',
            collect($manager->upgrades())
                ->pluck('to')
                ->unique()
                ->sort()
                ->map(fn (int $version) => (string) $version)
                ->values()
                ->all()
        ));

        try {
            $report = $manager->run(
                $to,
                (bool) $this->option('dry-run'),
                ! (bool) $this->option('skip-tests'),
                (bool) $this->option('skip-backup'),
                fn (string $type, string $line) => $this->output->write($line),
            );
        } catch (RuntimeException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        foreach ($report as $line) {
            $this->line($line);
        }

        $this->components->info('Atualização concluída.');

        return self::SUCCESS;
    }
}
