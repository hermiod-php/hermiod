<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector;

interface FactoryInterface
{
    public function createReflectorForClass(string $class): ReflectorInterface;
}