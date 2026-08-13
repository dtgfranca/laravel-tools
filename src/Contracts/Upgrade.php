<?php

namespace LaravelTools\Upgrade\Contracts;

use LaravelTools\Upgrade\Support\Instruction;

interface Upgrade
{
    public function from(): int;

    public function to(): int;

    /**
     * @return Instruction[]
     */
    public function instructions(): array;
}
