<?php

declare(strict_types=1);

namespace Hermiod\Resource\Name;

interface StrategyInterface
{
    public function format(string $name): string;
}
