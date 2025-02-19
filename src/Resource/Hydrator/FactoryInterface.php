<?php

declare(strict_types=1);

namespace Hermiod\Resource\Hydrator;

interface FactoryInterface
{
    public function createHydratorForClass(string $class): HydratorInterface;
}