<?php

declare(strict_types=1);

namespace Hermiod\Resource;

interface FactoryInterface
{
    public function createReflectorForClass(string $class): ResourceInterface;
}