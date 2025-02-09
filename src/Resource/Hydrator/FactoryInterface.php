<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Hydrator;

interface FactoryInterface
{
    public function createHydratorForClass(string $class): HydratorInterface;
}