<?php

namespace LaravelTools\Upgrade\Support;

final class Instructions
{
    public static function setPhp(string $constraint): Instruction
    {
        return new Instruction(InstructionType::SetPhp, ['constraint' => $constraint]);
    }

    public static function require(string $package, string $constraint): Instruction
    {
        return new Instruction(InstructionType::RequirePackage, [
            'package' => $package,
            'constraint' => $constraint,
        ]);
    }

    public static function requireDev(string $package, string $constraint): Instruction
    {
        return new Instruction(InstructionType::RequireDevPackage, [
            'package' => $package,
            'constraint' => $constraint,
        ]);
    }

    public static function update(string $package, string $constraint, bool $dev = false): Instruction
    {
        return new Instruction(InstructionType::UpdatePackage, [
            'package' => $package,
            'constraint' => $constraint,
            'dev' => $dev,
        ]);
    }

    public static function remove(string $package): Instruction
    {
        return new Instruction(InstructionType::RemovePackage, ['package' => $package]);
    }

    /**
     * @param  string[]  $paths
     * @param  string[]  $extensions
     */
    public static function replaceInFiles(
        string $search,
        string $replace,
        array $paths = ['app', 'tests', 'bootstrap', 'config'],
        array $extensions = ['php'],
        bool $regex = false,
    ): Instruction {
        return new Instruction(InstructionType::ReplaceInFiles, [
            'search' => $search,
            'replace' => $replace,
            'paths' => $paths,
            'extensions' => $extensions,
            'regex' => $regex,
        ]);
    }

    public static function setConfigValue(string $file, string $key, string $value): Instruction
    {
        return new Instruction(InstructionType::SetConfigValue, [
            'file' => $file,
            'key' => $key,
            'value' => $value,
        ]);
    }

    public static function replaceInFile(string $file, string $search, string $replace, bool $regex = false): Instruction
    {
        return new Instruction(InstructionType::ReplaceInFile, [
            'file' => $file,
            'search' => $search,
            'replace' => $replace,
            'regex' => $regex,
        ]);
    }

    public static function publishMigrations(string $package, string $tag): Instruction
    {
        return new Instruction(InstructionType::PublishMigrations, [
            'package' => $package,
            'tag' => $tag,
        ]);
    }

    /**
     * @param  string[]  $sets
     */
    public static function rectorSets(array $sets): Instruction
    {
        return new Instruction(InstructionType::RectorSets, ['sets' => $sets]);
    }
}
