<?php

declare(strict_types=1);

namespace Hermiod\Resource;

interface FactoryInterface
{
    public function createResourceForClass(string $class): ResourceInterface;
}