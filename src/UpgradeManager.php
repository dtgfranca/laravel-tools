<?php

namespace LaravelTools\Upgrade;

use Closure;
use Illuminate\Support\Facades\Process;
use LaravelTools\Upgrade\Contracts\Upgrade;
use LaravelTools\Upgrade\Support\BackupManager;
use LaravelTools\Upgrade\Support\ComposerJson;
use LaravelTools\Upgrade\Support\FileReplacer;
use LaravelTools\Upgrade\Support\Instruction;
use LaravelTools\Upgrade\Support\InstructionType;
use LaravelTools\Upgrade\Support\MigrationPublisher;
use LaravelTools\Upgrade\Support\RectorRunner;
use LaravelTools\Upgrade\Support\VersionDetector;
use RuntimeException;

class UpgradeManager
{
    public function __construct(
        protected VersionDetector $detector,
        protected ComposerJson $composer,
        protected BackupManager $backup,
        protected RectorRunner $rector,
        protected MigrationPublisher $publisher,
        protected FileReplacer $replacer,
        protected array $upgrades = [],
    ) {}

    /**
     * @return Upgrade[]
     */
    public function upgrades(): array
    {
        return $this->upgrades;
    }

    public function find(int $from, int $to): ?Upgrade
    {
        foreach ($this->upgrades as $upgrade) {
            if ($upgrade->from() === $from && $upgrade->to() === $to) {
                return $upgrade;
            }
        }

        return null;
    }

    public function run(
        int $to,
        bool $dryRun = false,
        bool $runTests = true,
        bool $skipBackup = false,
        ?Closure $output = null,
    ): array {
        $current = $this->detector->major();

        if ($current === null) {
            throw new RuntimeException('Não foi possível detectar a versão atual do Laravel.');
        }

        $upgrade = $this->find($current, $to);

        if ($upgrade === null) {
            throw new RuntimeException("Nenhum caminho de upgrade de Laravel {$current} para {$to}.");
        }

        $report = ["Atualizando Laravel {$current} -> {$to}."];

        $this->composer->read();

        if (! $skipBackup && ! $dryRun && config('laravel-upgrade.backup.enabled', true)) {
            $dir = $this->backup->create(
                (array) config('laravel-upgrade.backup.paths', []),
                (string) config('laravel-upgrade.backup.directory', 'backup_pre_update')
            );

            $report[] = "Backup criado em: {$dir}";
        }

        $instructions = $upgrade->instructions();

        $pre = array_filter(
            $instructions,
            fn (Instruction $i) => in_array($i->type, [
                InstructionType::SetPhp,
                InstructionType::RequirePackage,
                InstructionType::RequireDevPackage,
                InstructionType::UpdatePackage,
                InstructionType::RequireIfPresent,
                InstructionType::RemovePackage,
                InstructionType::ReplaceInFile,
                InstructionType::ReplaceInFiles,
                InstructionType::SetConfigValue,
            ], true)
        );

        $post = array_filter(
            $instructions,
            fn (Instruction $i) => in_array($i->type, [
                InstructionType::RectorSets,
                InstructionType::PublishMigrations,
            ], true)
        );

        foreach ($pre as $instruction) {
            array_push($report, ...$this->execute($instruction, $dryRun));
        }

        if (! $dryRun) {
            $this->composer->write();
            array_push($report, ...$this->runComposerUpdate($output));
            array_push($report, ...$this->clearCaches());
        } else {
            $report[] = '[dry-run] composer.json e arquivos NÃO foram modificados.';
        }

        foreach ($post as $instruction) {
            array_push($report, ...$this->execute($instruction, $dryRun, $output));
        }

        if ($runTests && ! $dryRun && config('laravel-upgrade.tests.enabled', true)) {
            array_push($report, ...$this->runTests($output));
        }

        $report[] = 'Concluído. Revise as mudanças e rode os testes com atenção.';

        return $report;
    }

    protected function execute(Instruction $instruction, bool $dryRun, ?Closure $output = null): array
    {
        return match ($instruction->type) {
            InstructionType::SetPhp => $this->applySetPhp($instruction, $dryRun),
            InstructionType::RequirePackage => $this->applyRequire($instruction, $dryRun),
            InstructionType::RequireDevPackage => $this->applyRequireDev($instruction, $dryRun),
            InstructionType::UpdatePackage => $this->applyUpdatePackage($instruction, $dryRun),
            InstructionType::RequireIfPresent => $this->applyRequireIfPresent($instruction, $dryRun),
            InstructionType::RemovePackage => $this->applyRemove($instruction, $dryRun),
            InstructionType::ReplaceInFile => $this->applyReplaceInFile($instruction, $dryRun),
            InstructionType::ReplaceInFiles => $this->applyReplaceInFiles($instruction, $dryRun),
            InstructionType::SetConfigValue => $this->applySetConfigValue($instruction, $dryRun),
            InstructionType::RectorSets => $dryRun
                ? ['[dry-run] Rector seria executado com os sets: '.implode(', ', $instruction->data['sets'])]
                : $this->rector->run($instruction->data['sets'], $output),
            InstructionType::PublishMigrations => $dryRun
                ? ["[dry-run] Publicaria migrations de {$instruction->data['package']} (tag: {$instruction->data['tag']})."]
                : $this->publisher->publish($instruction->data['package'], $instruction->data['tag'], $output),
        };
    }

    protected function applySetPhp(Instruction $instruction, bool $dryRun): array
    {
        $constraint = $instruction->data['constraint'];

        if ($dryRun) {
            return ["[dry-run] composer.json: php -> {$constraint}"];
        }

        $this->composer->setPhp($constraint);

        return ["composer.json: php -> {$constraint}"];
    }

    protected function applyRequire(Instruction $instruction, bool $dryRun): array
    {
        $package = $instruction->data['package'];
        $constraint = $instruction->data['constraint'];

        if ($dryRun) {
            return ["[dry-run] composer.json: require {$package} -> {$constraint}"];
        }

        $this->composer->requirePackage($package, $constraint);

        return ["composer.json: require {$package} -> {$constraint}"];
    }

    protected function applyRequireDev(Instruction $instruction, bool $dryRun): array
    {
        $package = $instruction->data['package'];
        $constraint = $instruction->data['constraint'];

        if ($dryRun) {
            return ["[dry-run] composer.json: require-dev {$package} -> {$constraint}"];
        }

        $this->composer->requireDevPackage($package, $constraint);

        return ["composer.json: require-dev {$package} -> {$constraint}"];
    }

    protected function applyUpdatePackage(Instruction $instruction, bool $dryRun): array
    {
        $package = $instruction->data['package'];
        $constraint = $instruction->data['constraint'];
        $dev = (bool) $instruction->data['dev'];

        if ($dryRun) {
            return ["[dry-run] composer.json: atualizar {$package} -> {$constraint} (se presente)"];
        }

        $updated = $this->composer->updatePackage($package, $constraint, $dev);

        return $updated
            ? ["composer.json: {$package} -> {$constraint}"]
            : ["composer.json: {$package} não está instalado. Versão mantida."];
    }

    protected function applyRequireIfPresent(Instruction $instruction, bool $dryRun): array
    {
        $package = $instruction->data['package'];
        $constraint = $instruction->data['constraint'];
        $ifPackage = $instruction->data['if'];

        if ($dryRun) {
            return ["[dry-run] composer.json: require {$package} -> {$constraint} (se {$ifPackage} estiver presente)"];
        }

        if (! $this->composer->hasPackage($ifPackage)) {
            return ["composer.json: {$ifPackage} não está instalado. {$package} não será adicionado."];
        }

        $this->composer->requirePackage($package, $constraint);

        return ["composer.json: require {$package} -> {$constraint}"];
    }

    protected function applyRemove(Instruction $instruction, bool $dryRun): array
    {
        $package = $instruction->data['package'];

        if ($dryRun) {
            return ["[dry-run] composer.json: remover {$package}"];
        }

        $this->composer->removePackage($package);

        return ["composer.json: removido {$package}"];
    }

    protected function applyReplaceInFile(Instruction $instruction, bool $dryRun): array
    {
        $path = base_path($instruction->data['file']);

        if (! is_file($path)) {
            return ["Arquivo {$instruction->data['file']} não encontrado. Substituição ignorada."];
        }

        $contents = (string) file_get_contents($path);
        $search = $instruction->data['search'];
        $replace = $instruction->data['replace'];

        $updated = $instruction->data['regex']
            ? preg_replace($search, $replace, $contents)
            : str_replace($search, $replace, $contents);

        if ($updated === null || $updated === $contents) {
            return ["Nenhuma alteração necessária em {$instruction->data['file']}."];
        }

        if ($dryRun) {
            return ["[dry-run] {$instruction->data['file']} seria modificado."];
        }

        file_put_contents($path, $updated);

        return ["{$instruction->data['file']} modificado."];
    }

    protected function applyReplaceInFiles(Instruction $instruction, bool $dryRun): array
    {
        $search = $instruction->data['search'];
        $replace = $instruction->data['replace'];
        $paths = $instruction->data['paths'];
        $extensions = $instruction->data['extensions'];
        $regex = (bool) $instruction->data['regex'];

        if ($dryRun) {
            return ["[dry-run] Substituiria '{$search}' por '{$replace}' em ".implode(', ', $paths).'.'];
        }

        $changed = $this->replacer->replaceInFiles($search, $replace, $paths, $extensions, $regex);

        if ($changed === []) {
            return ["Nenhuma ocorrência de '{$search}' encontrada em ".implode(', ', $paths).'.'];
        }

        return ["'{$search}' -> '{$replace}' aplicado em ".count($changed).' arquivo(s): '.implode(', ', $changed)];
    }

    protected function applySetConfigValue(Instruction $instruction, bool $dryRun): array
    {
        if ($dryRun) {
            return ["[dry-run] Adicionaria '{$instruction->data['key']}' em {$instruction->data['file']}."];
        }

        return [$this->replacer->setConfigValue(
            $instruction->data['file'],
            $instruction->data['key'],
            $instruction->data['value'],
        )];
    }

    protected function runComposerUpdate(?Closure $output = null): array
    {
        $result = Process::path(base_path())
            ->timeout(0)
            ->run((array) config('laravel-upgrade.composer.update_command'), $output);

        return [
            $result->successful()
                ? 'composer update concluído.'
                : 'composer update falhou: '.trim($result->errorOutput()),
        ];
    }

    protected function clearCaches(): array
    {
        $commands = ['config:clear', 'route:clear', 'view:clear', 'cache:clear'];

        $messages = [];

        foreach ($commands as $command) {
            $result = Process::path(base_path())->run(['php', 'artisan', $command]);

            $messages[] = $result->successful()
                ? "artisan {$command} executado."
                : "artisan {$command} falhou: ".trim($result->errorOutput());
        }

        return $messages;
    }

    protected function runTests(?Closure $output = null): array
    {
        $result = Process::path(base_path())
            ->timeout(0)
            ->run((array) config('laravel-upgrade.tests.command'), $output);

        return [
            $result->successful()
                ? 'Suíte de testes passou.'
                : 'Suíte de testes falhou: '.trim($result->errorOutput()),
        ];
    }
}
