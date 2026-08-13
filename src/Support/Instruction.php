<?php

namespace LaravelTools\Upgrade\Support;

final class Instruction
{
    public function __construct(
        public readonly InstructionType $type,
        public readonly array $data = [],
    ) {}
}
