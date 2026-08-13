<?php

namespace LaravelTools\Upgrade\Support;

use Illuminate\Support\Facades\Process;

class RectorRunner
{
    public function run(array $sets, ?\Closure $output = null): array
    {
        $binary = base_path((string) config('laravel-upgrade.rector.binary', 'vendor/bin/rector'));

        if (! is_file($binary)) {
            return ['Rector não está instalado. Transformações de código (AST) foram puladas.'];
        }

        $config = $this->writeConfig($sets);

        $result = Process::path(base_path())
            ->timeout(0)
            ->run([$binary, 'process', '--config='.$config, '--no-progress-bar'], $output);

        return [
            $result->successful()
                ? 'Rector aplicou as regras com sucesso.'
                : 'Rector finalizou com erros: '.trim($result->errorOutput()),
        ];
    }

    protected function writeConfig(array $sets): string
    {
        $file = base_path((string) config('laravel-upgrade.rector.config', 'rector-upgrade.php'));

        $paths = array_map(
            fn (string $path) => "        __DIR__.'/".trim($path, '/')."',",
            (array) config('laravel-upgrade.rector.paths', ['app', 'config', 'routes', 'database', 'bootstrap'])
        );

        $setLines = array_map(
            fn (string $set) => '        '.$set.',',
            $sets
        );

        $content = "<?php\n\n"
            ."use Rector\\Config\\RectorConfig;\n\n"
            ."return RectorConfig::configure()\n"
            ."    ->withPaths([\n"
            .implode("\n", $paths)."\n"
            ."    ])\n"
            ."    ->withSets([\n"
            .implode("\n", $setLines)."\n"
            ."    ]);\n";

        file_put_contents($file, $content);

        return $file;
    }
}
