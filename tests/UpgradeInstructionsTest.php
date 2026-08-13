<?php

namespace LaravelTools\Upgrade\Tests;

use LaravelTools\Upgrade\Contracts\Upgrade;
use LaravelTools\Upgrade\Support\InstructionType;
use LaravelTools\Upgrade\Upgrades\Laravel10;
use LaravelTools\Upgrade\Upgrades\Laravel11;
use LaravelTools\Upgrade\Upgrades\Laravel12;
use LaravelTools\Upgrade\Upgrades\Laravel13;
use LaravelTools\Upgrade\Upgrades\Laravel9;
use PHPUnit\Framework\TestCase;

class UpgradeInstructionsTest extends TestCase
{
    /**
     * @return Upgrade[]
     */
    private function upgrades(): array
    {
        return [
            new Laravel9(),
            new Laravel10(),
            new Laravel11(),
            new Laravel12(),
            new Laravel13(),
        ];
    }

    public function test_upgrade_paths_are_contiguous(): void
    {
        $upgrades = $this->upgrades();

        $this->assertSame(8, $upgrades[0]->from());
        $this->assertSame(9, $upgrades[0]->to());
        $this->assertSame(10, $upgrades[1]->to());
        $this->assertSame(11, $upgrades[2]->to());
        $this->assertSame(12, $upgrades[3]->to());
        $this->assertSame(13, $upgrades[4]->to());
    }

    public function test_every_upgrade_declares_framework_version(): void
    {
        foreach ($this->upgrades() as $upgrade) {
            $framework = collect($upgrade->instructions())
                ->first(fn ($i) => $i->type === InstructionType::RequirePackage
                    && $i->data['package'] === 'laravel/framework');

            $this->assertNotNull(
                $framework,
                "Upgrade {$upgrade->from()}->{$upgrade->to()} deve declarar laravel/framework"
            );

            $this->assertSame('^'.$upgrade->to().'.0', $framework->data['constraint']);
        }
    }

    public function test_instructions_are_well_formed(): void
    {
        foreach ($this->upgrades() as $upgrade) {
            $this->assertNotEmpty($upgrade->instructions());

            foreach ($upgrade->instructions() as $instruction) {
                if (in_array($instruction->type, [
                    InstructionType::RequirePackage,
                    InstructionType::RequireDevPackage,
                    InstructionType::UpdatePackage,
                ], true)) {
                    $this->assertNotEmpty($instruction->data['package']);
                    $this->assertNotEmpty($instruction->data['constraint']);
                }

                if ($instruction->type === InstructionType::ReplaceInFile) {
                    $this->assertNotEmpty($instruction->data['file']);
                }

                if ($instruction->type === InstructionType::ReplaceInFiles) {
                    $this->assertNotEmpty($instruction->data['search']);
                    $this->assertNotEmpty($instruction->data['paths']);
                }

                if ($instruction->type === InstructionType::SetConfigValue) {
                    $this->assertNotEmpty($instruction->data['file']);
                    $this->assertNotEmpty($instruction->data['key']);
                }
            }
        }
    }
}
